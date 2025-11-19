<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Page\PageListItemDTO;
use WP_Post;

class PageService
{
    /**
     * Returns a list of all published pages sorted by menu order and post title.
     *
     * @return PageListItemDTO[]
     */
    public function all()
    {
        $pages = get_pages([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'sort_column' => 'menu_order,post_title',
            'sort_order'  => 'asc',
        ]);

        $result = [];

        foreach ($pages as $page) {
            $result[] = $this->format_data($page);
        }

        return $result;
    }

    /**
     * Formats a page into a PageListItemDTO.
     *
     * @param WP_Post $page The page to format.
     *
     * @return PageListItemDTO
     */
    protected function format_data(WP_Post $page)
    {
        $dto = new PageListItemDTO();
        $dto->id        = (string) $page->ID;
        $dto->name      = $page->post_title;
        $dto->slug      = get_page_uri($page->ID);
        $dto->url       =  get_page_link($page->ID);
        $dto->parent_id = (string) $page->post_parent;

        return $dto;
    }
}
