<?php

namespace App\Presenters\SemanticUi;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Pagination\Presenter as PresenterContract;
use Illuminate\Pagination\UrlWindow;

/**
 * Class PaginatorPresenter
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Presenter
 */
class PaginatorPresenter implements PresenterContract
{

    use \Illuminate\Pagination\UrlWindowPresenterTrait;

    /**
     * The paginator implementation.
     *
     * @var \Illuminate\Contracts\Pagination\Paginator
     */
    protected $paginator;
    /**
     * The URL window data structure.
     *
     * @var array
     */
    protected $window;


    /**
     * @param LengthAwarePaginatorContract $paginator
     * @param UrlWindow|null               $window
     */
    public function __construct(LengthAwarePaginatorContract $paginator,
                                UrlWindow $window = null)
    {
        $this->paginator = $paginator;
        $this->window    = is_null($window) ?
            UrlWindow::make($paginator) :
            $window->get();
    }

    /**
     * Convert the URL window into Bootstrap HTML.
     *
     * @return string
     */
    public function render($appendClasses = [])
    {
        if ($this->hasPages()) {
            return sprintf(
                '<div class="pull-right paging  ' . implode(' ', $appendClasses) . '">%s %s %s %s</div>',
                $this->dataCountSet(),
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            );
        }else{
            return sprintf(
                '<div class="pull-right paging  ' . implode(' ', $appendClasses) . '">%s </div>',
                $this->dataCountSet()
            );
        }

        return '';
    }

    protected function dataCountSet(){
        return  $this->paginator->total().'条记录 '.$this->paginator->currentPage().'/'.$this->paginator->lastPage().' 页 ';
    }

    /**
     * Determine if the underlying paginator being presented has pages to show.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->paginator->hasPages();
    }

    /**
     * Get HTML wrapper for an available page link.
     *
     * @param  string      $url
     * @param  int         $page
     * @param  string|null $rel
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page, $rel = null)
    {
        $rel = is_null($rel) ? '' : ' rel="' . $rel . '"';

        return '<a  href="' . $url . '"' . $rel . '>' . $page . '</a>';
    }

    /**
     * Get HTML wrapper for disabled text.
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<a >' . $text . '</a>';
    }

    /**
     * Get HTML wrapper for active text.
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<span class="current">' . $text . '</span>';
    }

    /**
     * Get a pagination "dot" element.
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper("...");
    }

    /**
     * Get the current page from the paginator.
     *
     * @return int
     */
    protected function currentPage()
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page from the paginator.
     *
     * @return int
     */
    protected function lastPage()
    {
        return $this->paginator->lastPage();
    }

    /**
     * Get the previous page pagination element.
     *
     * @param  string $text
     * @return string
     */
    protected function getPreviousButton($text = '上一页')
    {
        // If the current page is less than or equal to one, it means we can't go any
        // further back in the pages, so we will render a disabled previous button
        // when that is the case. Otherwise, we will give it an active "status".
        if ($this->paginator->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->paginator->url(
            $this->paginator->currentPage() - 1
        );

        return $this->getPageLinkWrapper($url, $text, 'prev');
    }

    /**
     * Get the next page pagination element.
     *
     * @param  string $text
     * @return string
     */
    protected function getNextButton($text = '下一页')
    {
        // If the current page is greater than or equal to the last page, it means we
        // can't go any further into the pages, as we're already on this last page
        // that is available, so we will make it the "next" link style disabled.
        if ( ! $this->paginator->hasMorePages()) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->paginator->url($this->paginator->currentPage() + 1);

        return $this->getPageLinkWrapper($url, $text, 'next');
    }
}