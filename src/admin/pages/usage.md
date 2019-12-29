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
        ->setConfig('pageTitle', __('Notifications'))
        ->setConfig('menuTitle', __('Notifications'))
        ->setConfig('icon', 'dashicons-testimonial')
        ->setConfig('position', 20)
        ->registerAdminSubpage(new MyAwesomePage) // <<< Menu page
        ->registerAdminSubpage(new MyAwesomePage2) // <<< Submenu page
        ->registerAdminSubpage(new MyAwesomePage3) // <<< Submenu page
        ->init();
```