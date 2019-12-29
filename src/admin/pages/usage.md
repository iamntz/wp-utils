### 1. Implement SubPage Interface:

```php
class MyAwesomePage implements SubPage {

    public function getSlug(): string
    {
        return 'my-awesome-page-slug';
    }

    public function getTitle(): string
    {
        return __('MyAwesomePage');
    }

    public function render(): void
    {
        echo 'yay';
    }

    public function getCap(): string
    {
        return 'edit_others_posts';
    }
}
```

### 2. Initialize the page class:
```php
    (new Page)
        ->setConfig('icon', 'dashicons-testimonial')
        ->setConfig('position', 20)
        ->registerAdminSubpage(new MyAwesomePage) // <<< Menu page
        ->registerAdminSubpage(new MyAwesomePage2) // <<< Submenu page
        ->registerAdminSubpage(new MyAwesomePage3) // <<< Submenu page
        ->init();
```

## Considerations

1. First registered page will act as level 0 menu page (i.e. it will be visible in the WP dashboard sidebar)
2. This page should have a lower or equal capability compared to others, in order to make other pages visible to lower cap users.