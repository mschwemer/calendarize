<?php

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Model\Dto\Search;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Event\IndexRepositoryFindBySearchEvent;
use HDNET\Calendarize\Register;

class DefaultEventSearchListener
{
    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(IndexRepositoryFindBySearchEvent $event)
    {
        if (!\in_array(Register::UNIQUE_REGISTER_KEY, $event->getIndexTypes(), true)) {
            return;
        }

        $search = $this->getSearchDto($event);

        if (!$search->isSearch()) {
            return;
        }

        $searchTermIds = $this->eventRepository->findBySearch($search);
        // Blocks result (displaying no event) on no search match (empty id array)
        $searchTermIds[] = -1;

        $indexIds = $event->getIndexIds();
        $indexIds['tx_calendarize_domain_model_event'] = $searchTermIds;
        $event->setIndexIds($indexIds);
    }

    protected function getSearchDto(IndexRepositoryFindBySearchEvent $event): Search
    {
        $customSearch = $event->getCustomSearch();

        $search = new Search();
        $search->setFullText(trim((string)$customSearch['fullText'] ?? ''));
        $search->setCategory((int)$customSearch['category'] ?? 0);

        return $search;
    }
}
