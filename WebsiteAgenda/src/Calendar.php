<?php

namespace Calendar;

class Calendar
{
    private array $events = [];

    public function addEvent(Event $event): string
    {
        $this->events[] = $event;
        return "Event {$event->getTitle()} added to list!";
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getEventById(Int $eventId): Event|string
    {
        foreach ($this->events as $event) {
            if ($event->getId() === $eventId) {
                return $event;
            }
        }
        return "Event with {$eventId} id not found!";
    }

    public function getEventByTitle(string $title): Event|string
    {
        foreach ($this->events as $event) {
            if ($event->getTitle() === $title) {
                return $event;
            }
        }
        return "Event {$title} not found!";
    }

    public function getEventsByDate(int $date): array
    {
        $events = [];
        foreach ($this->events as $event) {
            if (mktime(0, 0, 0, date("m", $date), date("d", $date), date("Y", $date)) <= $event->getDate()
                && $event->getDate() < mktime(0, 0, 0, date("m", $date), (int)date("d", $date) + 1, date("Y", $date))) {
                $events[] = $event;
            }
        }
        usort($events, function ($a, $b) {
            return $a->getDate() <=> $b->getDate();
        });
        return $events;
    }

    public function removeEvent(Event $event): string
    {
        $key = array_search($event, $this->events);
        if ($key !== false) {
            unset($this->events[$key]);
            return "Event {$event->getTitle()} removed from list!";
        }
        return "Event {$event->getTitle()} not found!";
    }

    public function generateMonth(int $referenceTime): string
    {
        $output = "<tr>";

        for ($i = 1; $i < date("N", strtotime("01-". date("m-Y", $referenceTime))); $i++) {
            $output .= "<td class='py-4 text-muted'></td>";
        }

        for ($day = 1; $day <= date("t", $referenceTime); $day++) {
            if (mktime(0, 0, 0, date("m", $referenceTime), $day, date("Y", $referenceTime)) <= time()
                && time() < mktime(0, 0, 0, date("m", $referenceTime), $day + 1, date("Y", $referenceTime))) {
                $output .= "<td class='py-4 position-relative bg-warning-subtle'>";
            } else {
                $output .= "<td class='py-4 position-relative'>";
            }
            $output .= "<a href='index.php?page=calendarDay&time=".mktime(0, month: date("m", $referenceTime), day: $day, year: date("Y", $referenceTime))."' class='link-secondary fw-bold position-absolute top-0 start-0 ms-1 small'>$day</a>";
            foreach ($this->getEventsByDate(strtotime("$day-". date("m-Y", $referenceTime))) as $event) {
                $output .= '<button class="btn badge" style="background-color:'.$event->getColor().'" data-bs-toggle="modal" data-bs-target="#'. $event->getId() .'Modal">' . $event->getTitle() . '</button>';
                $output .= $event->generateEventModal();
            }
            $output .= "</td>";
            if (date("N", strtotime("$day-". date("m-Y", $referenceTime))) == 7 ) {
                $output .= "</tr><tr>";
            }
        }

        for ($i = date("N", strtotime(date("t", $referenceTime) . "-". date("m-Y", $referenceTime))); $i < 7; $i++) {
            $output .= "<td class='py-4 text-muted'></td>";
        }
        return $output . "</tr>";
    }

    public function generateWeek(int $referenceTime): string
    {
        $output = "";
        $startWeekDay = (int)date("d", $referenceTime) - (int)date("N", $referenceTime) + 1;

        for ($day = $startWeekDay; $day < $startWeekDay + 7; $day++) {
            if (mktime(0, 0, 0, date("m", $referenceTime), $day, date("Y", $referenceTime)) < time()
                && time() < mktime(23, 59, 59, date("m", $referenceTime), $day, date("Y", $referenceTime))) {
                $output .= '<div class="card shadow-sm flex-fill bg-warning-subtle"><a href="index.php?page=calendarDay&time='.mktime(0, month: date("m", $referenceTime), day: $day, year: date("Y", $referenceTime)).'" class="btn card-header text-center fw-bold">';
            }
            else {
                $output .= '<div class="card shadow-sm flex-fill"><a href="index.php?page=calendarDay&time='.mktime(0, month: date("m", $referenceTime), day: $day, year: date("Y", $referenceTime)).'" class="btn card-header text-center fw-bold">';
            }
            $output .= date("l d", mktime(0, month: date("m", $referenceTime), day: date("d", mktime(0, day: $day)), year: date("Y", $referenceTime)));
            $output .= '</a><div class="card-body small">';
            foreach ($this->getEventsByDate(mktime(0, month: date("m", $referenceTime), day: date("d", mktime(0, day: $day)), year: date("Y", $referenceTime))) as $event) {
                $output .= '<button class="btn btn-sm" style="background-color:'.$event->getColor().'" data-bs-toggle="modal" data-bs-target="#'. $event->getId() .'Modal">' . date("H:i", $event->getDate()) . " — " . $event->getTitle() . "</button><br>";
                $output .= $event->generateEventModal();
            }
            $output .= '</div></div>';
        }
        return $output;
    }

    public function generateDay(int $referenceTime): string
    {
        foreach ($this->getEventsByDate($referenceTime) as $event) {
            $output = ($output ?? "") . '<div class="card shadow-sm mb-2"> <button class="btn w-100 text-start p-0" data-bs-toggle="modal" data-bs-target="#' . $event->getId() . 'Modal"><div class="card-header fw-bold">' . date("H:i", $event->getDate()) . ' — ' . $event->getTitle() . '</div>';
            $output .= '<div class="card-body small"><p class="mb-0">' . $event->getDescription() . '</p></div></button></div>';
            $output .= $event->generateEventModal();
        }
        return $output ?? 'Nothing planned';
    }
}