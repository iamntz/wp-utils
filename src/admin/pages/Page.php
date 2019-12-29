<?php

namespace iamntz\wpUtils\admin\pages;

class Page
{
    private $pages = [];

    private $config = [
        'icon' => 'dashicons-testimonial',
        'position' => 20,
    ];

    public function __construct()
    {
    }

    public function init()
    {
        add_action('admin_menu', [$this, 'registerAdminPages']);
    }

    public function registerAdminSubpage(SubPage $page)
    {
        $this->pages[$page->getSlug()] = $page;

        return $this;
    }

    public function setConfig($key, $value)
    {
        if (isset($this->config[$key])) {
            $this->config[$key] = $value;
        }

        return $this;
    }

    public function registerAdminPages()
    {
        $this->pages = array_values($this->pages);

        if (empty($this->pages)) {
            return;
        }

        add_menu_page(
            $this->pages[0]->getTitle(),
            $this->pages[0]->getTitle(),
            $this->pages[0]->getCap(),
            $this->pages[0]->getSlug(),
            [$this->pages[0], 'render'],
            $this->config['icon'],
            $this->config['position']
        );

        foreach ($this->pages as $subpage) {
            add_submenu_page(
                $this->pages[0]->getSlug(),
                $subpage->getTitle(),
                $subpage->getTitle(),
                $subpage->getCap(),
                $subpage->getSlug(),
                [$subpage, 'render']
            );
        }
    }
}