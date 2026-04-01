<?php
declare(strict_types=1);

namespace App\Core;

class Pagination {
    
    /**
     * Get pagination parameters.
     *
     * @param int $totalItems
     * @param int $perPage
     * @return array
     */
    public static function getParams(int $totalItems, int $perPage = 10): array {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $totalPages = (int)ceil($totalItems / $perPage);
        $page = min($page, $totalPages > 0 ? $totalPages : 1);
        
        $offset = ($page - 1) * $perPage;
        
        return [
            'page' => $page,
            'limit' => $perPage,
            'offset' => $offset,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ];
    }
    
    /**
     * Render the pagination HTML.
     *
     * @param int $currentPage
     * @param int $totalPages
     * @param string $baseUrl
     * @param int|null $totalItems
     * @param int $perPage
     * @return string
     */
    public static function render(int $currentPage, int $totalPages, string $baseUrl, ?int $totalItems = null, int $perPage = 25): string {
        if ($totalPages <= 1 && $totalItems === null) {
            return '';
        }
        
        // Build base URL without existing 'page' parameter
        $urlParts = parse_url($baseUrl);
        $queryParams = [];
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
        }
        $currentGet = $_GET;
        unset($currentGet['page']);
        $allParams = array_merge($queryParams, $currentGet);
        
        $path = $urlParts['path'] ?? '';
        $base = $path . (!empty($allParams) ? '?' . http_build_query($allParams) . '&' : '?');
        
        $html = '<div class="pagination-container">';
        
        // Pagination Info
        if ($totalItems !== null) {
            $from = ($currentPage - 1) * $perPage + 1;
            $to = min($currentPage * $perPage, $totalItems);
            $html .= sprintf('<div class="pagination-info">Mostrando <strong>%d</strong> a <strong>%d</strong> de <strong>%d</strong> registros</div>', $from, $to, $totalItems);
        } else {
            $html .= '<div></div>'; // Spacer if no info
        }
        
        $html .= '<ul class="pagination-list">';
        
        // First & Previous
        if ($currentPage > 1) {
            $html .= sprintf('<li class="pagination-item nav-btn"><a href="%spage=1" title="Primeira Página"><i data-lucide="chevrons-left"></i></a></li>', $base);
            $html .= sprintf('<li class="pagination-item nav-btn"><a href="%spage=%d" title="Anterior"><i data-lucide="chevron-left"></i></a></li>', $base, $currentPage - 1);
        } else {
            $html .= '<li class="pagination-item nav-btn disabled"><span><i data-lucide="chevrons-left"></i></span></li>';
            $html .= '<li class="pagination-item nav-btn disabled"><span><i data-lucide="chevron-left"></i></span></li>';
        }
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        if ($start > 1) {
            $html .= '<li class="pagination-item disabled"><span>...</span></li>';
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = ($i === $currentPage) ? ' active' : '';
            if ($i === $currentPage) {
                $html .= sprintf('<li class="pagination-item%s"><span>%d</span></li>', $activeClass, $i);
            } else {
                $html .= sprintf('<li class="pagination-item%s"><a href="%spage=%d">%d</a></li>', $activeClass, $base, $i, $i);
            }
        }
        
        if ($end < $totalPages) {
            $html .= '<li class="pagination-item disabled"><span>...</span></li>';
        }
        
        // Next & Last
        if ($currentPage < $totalPages) {
            $html .= sprintf('<li class="pagination-item nav-btn"><a href="%spage=%d" title="Próxima"><i data-lucide="chevron-right"></i></a></li>', $base, $currentPage + 1);
            $html .= sprintf('<li class="pagination-item nav-btn"><a href="%spage=%d" title="Última Página"><i data-lucide="chevrons-right"></i></a></li>', $base, $totalPages);
        } else {
            $html .= '<li class="pagination-item nav-btn disabled"><span><i data-lucide="chevron-right"></i></span></li>';
            $html .= '<li class="pagination-item nav-btn disabled"><span><i data-lucide="chevrons-right"></i></span></li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
}
