<?php

namespace Iekadou\Quickies;

use Lare_Team\Lare\Lare as Lare;


class View extends Renderable
{
    public function _post_construct() {
        // Lare
        Lare::set_current_namespace(LARE_PREFIX.'.'.$this->id);
        $this->set_template_var('lare_matching', Lare::get_matching_count());
        $this->set_template_var('lare_current_namespace', Lare::get_current_namespace());
        $this->set_template_var('title', SITE_NAME.' - '.$this->name);
        $this->set_template_var('LARE_PREFIX', LARE_PREFIX);
    }
}
