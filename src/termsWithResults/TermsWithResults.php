<?php

namespace iamntz\wpUtils\termsWithResults;

use WP_Term;

final class TermsWithResults
{
    private $get_terms_args;
    private $dependsOn;
    private $postType;
    private $validTerms = [];

    private $allTerms;

    /**
     *
     * @param $get_terms_args array  are the same params you can find in get_terms
     * @param $dependsOn array WP_Term[] the terms the query will depend on
     * @param string $postType
     */
    public function __construct(array $get_terms_args, array $dependsOn = [], string $postType = 'post')
    {
        $this->get_terms_args = $get_terms_args;
        $this->dependsOn = $dependsOn;
        $this->postType = $postType;

        $this->allTerms = get_terms($get_terms_args);
    }

    /**
     * @return WP_Term[]
     */
    public function getValidTerms(): array
    {
        if (empty($this->dependsOn)) {
            return $this->allTerms;
        }

        $valid = $this->validateTerms();

        return array_values(array_filter($this->allTerms, function ($term) use ($valid) {
            return in_array($term->term_id, $valid);
        }));
    }

    public function _validateTerm(WP_Term $term)
    {
        global $wpdb;

        $term_tax_id = $this->getTermTaxIDQuery($term);

        if (!$term_tax_id) {
            $this->validTerms[] = null;
            return;
        }

        $posts_with_same_term = $this->getPostsWithinSameTerm($term_tax_id);

        if (empty($posts_with_same_term)) {
            $this->validTerms[] = null;
            return;
        }

        $terms = $this->getMatchingTerms(wp_list_pluck($posts_with_same_term, 'object_id'));

        if (empty($terms)) {
            $this->validTerms[] = null;
            return;
        }

        $this->validTerms[] = wp_list_pluck($terms, 'term_id');
    }

    private function getTermTaxIDQuery(WP_Term $term): string
    {
        global $wpdb;

        $q = "SELECT tt.term_taxonomy_id 
            FROM $wpdb->terms t 
                INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id 
            WHERE 
                  t.term_id = '%s' AND 
                  tt.taxonomy = '%s' 
            LIMIT 1";

        $q = $wpdb->prepare($q, $term->term_id, $term->taxonomy);

        return $this->query($q, 'get_var');
    }

    private function getPostsWithinSameTerm(int $term_tax_id): array
    {
        global $wpdb;

        $q = "SELECT tr.object_id 
            FROM $wpdb->term_relationships tr 
                INNER JOIN $wpdb->posts p ON p.ID = tr.object_id 
            WHERE 
                  term_taxonomy_id = %s AND 
                  p.post_status = 'publish' AND 
                  p.post_type = '{$this->postType}'";

        $q = $wpdb->prepare($q, $term_tax_id);

        return $this->query($q, 'get_results');
    }

    private function getMatchingTerms(array $postIDs): array
    {
        global $wpdb;
        $taxonomy = $this->get_terms_args['taxonomy'];

        $postIDs = implode(',', array_map('absint', $postIDs));

        // we NEED `sprintf` because wp_prepare will quote the whole thing, and we don't want that.
        $q = sprintf("SELECT DISTINCT t.term_id, t.name 
            FROM $wpdb->term_taxonomy tt 
                INNER JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id 
                INNER JOIN wp_terms t ON t.term_id = tt.term_id 
            WHERE tt.taxonomy = '{$taxonomy}' 
              AND tr.object_id IN (%s)", $postIDs);

        return $this->query($q, 'get_results');
    }

    private function validateTerms(): array
    {
        array_walk($this->dependsOn, [$this, '_validateTerm']);

        return array_reduce($this->validTerms, function ($carry, $item) {
                if (is_null($carry)) {
                    return $item;
                }

                return array_intersect($carry, $item);
            }, null) ?? [];
    }

    private function query(string $query, string $method)
    {
        global $wpdb;

        if (!is_callable([$wpdb, $method])) {
            throw new \Exception('Invalid Query Method');
        }

        $cacheKey = 'terms_with_results_' . sha1($query);

        $results = wp_cache_get($cacheKey);

        if (!$results) {
            $results = call_user_func([$wpdb, $method], $query);
            wp_cache_set($cacheKey, $results);
        }

        return $results;
    }
}
