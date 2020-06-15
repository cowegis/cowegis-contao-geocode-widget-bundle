<?php

declare(strict_types=1);

namespace %namespace%;

%imports%

final class %name% extends ObjectBehavior
{
    public function it_is_initializable() : void
    {
        $this->shouldHaveType(%subject_class%::class);
    }
}
