
class CowegisGeocodeAbstractPicker {
    constructor(map, options) {
        this.options = options;
        this.map = map;
        this.marker = null;
    }

    show(position, radius) {
        if (!this.marker) {
            this._createMarker(position, radius);
        } else {
            this._updateCoordinates(position, radius);
        }

        this._panTo(position);
    }

    _updateCoordinates(position) {
        this.marker.setLatLng(position);
    }
}

class CowegisGeocodeMarkerPicker extends CowegisGeocodeAbstractPicker {
    apply(coordinatesInput) {
        var coordinates = this.marker
            ? ( this.marker.getLatLng().lat + ',' + this.marker.getLatLng().lng)
            : '';

        coordinatesInput.set('value', coordinates);
    }

    _panTo(position) {
        this.map.map.setZoom(this.map.map.getMaxZoom());
        this.map.map.panTo(position);
    }

    _createMarker(position) {
        this.marker = this.map.leaflet.marker(position, {draggable: true}).addTo(this.map.map);
        this.marker.on('dragend', function () {
            this.map.map.panTo(this.marker.getLatLng());
        }.bind(this));
    }
}

class CowegisGeocodeCirclePicker extends CowegisGeocodeAbstractPicker {
    apply(coordinatesInput, radiusInput) {
        var radius      = '';
        var coordinates = this.marker
            ? ( this.marker.getLatLng().lat + ',' + this.marker.getLatLng().lng)
            : '';

        coordinatesInput.set('value', coordinates);

        if (this.marker) {
            radius = Math.round(this.marker.getRadius());

            if (this.options.radius.steps > 0) {
                radius = (this.options.radius.steps * Math.round(radius / this.options.radius.steps));
            }
        }

        radiusInput.set('value', radius);
    }

    _panTo() {
        this.map.map.fitBounds(this.marker.getBounds());
    }

    _createMarker(position, radius) {
        this.marker = this.map.leaflet.circle(position, { radius: radius || this.options.radius.default, pmIgnore: false });
        this.marker.addTo(this.map.map);
        this.marker.on('pm:enable', function () {
            this.map.map.fitBounds(this.marker.getBounds());
        }.bind(this));

        this.marker.on('pm:markerdragend', function () {
            var radius = this.marker.getRadius();

            if (this.options.radius.steps > 0) {
                radius = (this.options.radius.steps * Math.round(radius / this.options.radius.steps));
            }

            if (this.options.radius.min > 0 && this.options.radius.min > radius) {
                radius = this.options.radius.min;
            }

            if (this.options.radius.max > 0 && this.options.radius.max < radius) {
                radius = this.options.radius.max;
            }

            if (radius !== this.marker.getRadius()) {
                this.marker.setRadius(radius);
            } else {
                this.marker.pm._outerMarker.setTooltipContent(this._formatRadius(radius));
            }

            this.map.map.fitBounds(this.marker.getBounds());
        }.bind(this));

        this._enableEditMode();
    }

    _updateCoordinates(position,radius) {
        this.marker.pm.disable();
        this.marker.setLatLng(position);

        if (radius !== undefined) {
            this.marker.setRadius(radius);
        }
        this.marker.pm.enable();
    }

    _enableEditMode() {
        this.marker.pm.enable();
        this.marker.pm._outerMarker.bindTooltip(
            this._formatRadius(this.marker.getRadius()),
            {permanent: true, direction: 'right', offset: [10, 0] }
        );
    }

    _formatRadius(radius) {
        var unit = 'm';

        radius = Math.floor(radius);

        if (radius > 1000) {
            unit   = 'km';
            radius = (radius / 1000).toFixed(1);
        }

        return radius.toString() + ' ' + unit;
    }
}

class CowegisGeocodeWidget {
    constructor(options) {
        this.options = Object.assign(
            {
                urlTemplate: 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'
            },
            {
            mapTemplate: '<cowegis-editor id="cowegis_geocode_widget_map_{id}" class="cowegis-geocode-map" style="color-scheme: only light"></cowegis-editor>',
                modalWidth: 800,
                modalTitle: 'Choose coordinates',
                searchPositionLabel: 'Search',
                applyPositionLabel: 'Apply',
                confirmPositionLabel: 'Set as new position',
                okLabel: 'Ok',
                cancelLabel: 'Cancel',
                radius: null,
                picker: CowegisGeocodeMarkerPicker,
                bboxPadding: [0, 70],
                map: {
                    maxZoom: 15,
                    minZoom: 2,
                    center: [0,0],
                    zoom: 2
                }
            },
            options
        );

        this.mapOptions = {
            map: {
                options: this.options.map,
                controls: [{
                    controlId: 'geocoder',
                    type: 'geocoder',
                    options: {
                        defaultMarkGeocode: false,
                        collapsed: false,
                        placeholder: this.options.searchPositionLabel
                    }
                }],
                layers: [{
                    layerId: 'osm',
                    type: 'tileLayer',
                    urlTemplate: this.options.geocode.urlTemplate || 'https://{s}.tile.osm.org/{z}/{x}/{y}.png',
                    initialVisible: true,
                    options: {
                        attribution: this.options.geocode.attribution || '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
                    },
                }],
            },
            assets: [{
                type: 'stylesheet',
                url: '/bundles/cowegisclient/css/cowegis.css'
            }]
        };

        this.element = document.getElementById(this.options.id);
        this.toggle  = document.getElementById(this.options.id + '_toggle');
        this.toggle.addEvent('click', this._showMap.bind(this));

        if (this.options.radius) {
            this.radius = $(this.options.radius.element);

            if (this.radius.get('value').length > 0) {
                this.options.radius.default = parseInt(this.radius.get('value'));
            }

            if (this.options.radius.default === undefined) {
                this.options.radius.default = 0;
            }
        }
    }

    _template(str, data) {
        return str.replace(/\{ *([\w_-]+) *\}/g, function (str, key) {
            var value = data[key];

            if (value === undefined) {
                throw new Error('No value provided for variable ' + str);

            } else if (typeof value === 'function') {
                value = value(data);
            }
            return value;
        });
    }

    _showMap(e) {
        e.preventDefault();
        e.stopPropagation();

        // Create modal window.
        var content = this._template(this.options.mapTemplate, this.options);
        this.modal  = this._createModal();

        this.modal.show({title: this.options.modalTitle, contents: content});

        // Initialize map after showing modal so element exists.
        this._createMap();
    }

    _createModal() {
        var modal = new SimpleModal({
            width: this.options.modalWidth,
            hideFooter: false,
            draggable: false,
            overlayOpacity: .5,
            btn_ok: Contao.lang.close,
            onShow: function () {
                document.body.setStyle('overflow', 'hidden');
            },
            onHide: function () {
                document.body.setStyle('overflow', 'auto');
            }
        });

        modal.addButton(Contao.lang.apply, 'btn', function () {
            this.picker.apply(this.element, this.radius);
            modal.hide();
        }.bind(this));

        return modal;
    }

    _createMap() {
        var map = document.getElementById('cowegis_geocode_widget_map_' + this.options.id);
        var picker = new this.options.picker(map, this.options);
        var radius = 0;

        this.picker = picker;
        var pickerOptions = this.options;

        map.addEventListener('cowegis:ready', function () {
            map.controls.geocoder.on('markgeocode', function (event) {
                picker.show(event.geocode.center);
            })

            map.map.on('click', function (event) {
                var marker = new map.leaflet.marker(event.latlng).addTo(map.map);
                var container = document.createElement('div');
                var okButton = document.createElement('button');
                var cancelButton = document.createElement('button');

                okButton.set('class', 'cowegis-geocode-btn').appendHTML(pickerOptions.okLabel);
                okButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    picker.show(marker.getLatLng());
                    map.map.removeLayer(marker);
                }.bind(this));

                cancelButton.set('class', 'cowegis-geocode-btn').appendHTML(pickerOptions.cancelLabel);
                cancelButton.addEventListener('click', function (event) {
                    map.map.removeLayer(marker);
                });

                container.appendHTML('<h2>' + pickerOptions.confirmPositionLabel + '</h2>');
                container.appendChild(okButton);
                container.appendChild(cancelButton);

                marker.bindPopup(container, {
                    keepInView: true,
                    autoPanPaddingTopLeft: pickerOptions.bboxPadding,
                    autoClose: false,
                    closeOnClick: false,
                    closeButton: false
                }).openPopup();
            }.bind(this));

            if (this.element.value) {
                if (this.radius && this.radius.get('value').length > 0) {
                    radius = parseInt(this.radius.get('value'));
                }

                this.picker.show(map.leaflet.latLng(this.element.value.split(/,/)), radius);
            }
        }.bind(this));

        this.mapOptions.map.controls[0].options.query
            = this._createQuery(this.options.geocode.queryPattern, this.options.geocode.queryWidgetIds);

        map.config = this.mapOptions;
    }

    _createQuery(queryPattern = '', queryWidgetIds = []) {
        let widget;
        for (let i = 0; i < queryWidgetIds.length; i++) {
            if (!(widget = document.getElementById(queryWidgetIds[i]))) {
                continue;
            }

            queryPattern = queryPattern.replace(`#${queryWidgetIds[i]}#`, widget.value);
        }

        return queryPattern;
    }
}
