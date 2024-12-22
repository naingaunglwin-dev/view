<?php

namespace NALViewTest;

class CustomEngine
{
    public function render(array $views, array $data)
    {
        extract($data, EXTR_SKIP);
        ob_start();
        foreach ($views as $view) {
            include $view;
        }
        return ob_get_clean() ?: '';
    }
}