If you want to create a filter system based on terms, you'll hit a usability issue: you won't be able to see only valid tags. 

Since WP doesn't allow you to get only tags that have match for a post within a certain category, I've created this class.

## Usage:

Instead of `get_terms` you'll use `TermsWithResults->getValidTerms()`. The class will take up to three params:

- `array $get_terms_args`
- `array $dependsOn = []`
- `string $postType = 'post'`
  
### Example:

```php
$args = [
    'taxonomy' => 'post_tag',
];

$dependsOn = [
    get_term_by('name', 'seo', 'category'),
    get_term_by('name', 'marketing', 'campaign'),
    get_term_by('name', 'google', 'client'),
];

(new TermsWithResults($args, $dependsOn, 'portfolio'))->getValidTerms()
```

So in order to get a result, the tags (i.e. terms) returned should have posts that are matching:

- `seo` category
- `marketing` within `campaign` taxonomy
- `google` within `client` taxonomy

Please make sure that queries may not be the most performance-friendly, but should work just fine for small/medium sites.