<?php

namespace Growfund\Views\Components\Comments;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class EmptyComment extends View {

    protected function get_template_dir() {
        return 'site/components/comments';
    }
}
