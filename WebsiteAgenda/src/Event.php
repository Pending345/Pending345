<?php

namespace Calendar;

class Event
{
    private string $title;
    private string $description;
    private int $date;
    private int $id;
    private string $color;

    public function __construct()
    {
        $this->id = spl_object_id($this);
    }

    public function setEvent(string $title, string $description, int $date, string $color): void
    {
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setDate($date);
        $this->setColor($color);
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param Int $date
     */
    public function setDate(int $date): void
    {
        $this->date = $date;
    }
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Int
     */
    public function getDate(): int
    {
        return $this->date;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getColor(): string
    {
        $autoColor = $this->getColorByTitle();

        if (!empty($autoColor)) {
            return $autoColor;
        }

        return $this->color;
    }

    public function getColorByTitle(): string
    {
        $title = strtolower($this->title);

        if (str_contains($title, 'meeting')) return '#0dcaf0';
        if (str_contains($title, 'exam')) return '#dc3545';
        if (str_contains($title, 'birthday')) return '#ffc107';
        if (str_contains($title, 'kerst')) return '#198754';

        else return '';
    }

    public function generateEventModal(): string
    {
        $output = '<div class="modal fade" id="'.$this->getId().'Modal" tabindex="-1" aria-labelledby="'.$this->getId().'ModalLabel" aria-hidden="true">';
        $output .= '<div class="modal-dialog modal-dialog-centered">';
        $output .= '<div class="modal-content text-start">';
        $output .= '<div class="modal-header">';
        $output .= '<h1 class="modal-title fs-5" id="'.$this->getId().'ModalLabel">Modify event</h1>';
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $output .= '</div>';
        $output .= '<form action="index.php?page=updateEvent" method="POST">';
        $output .= '<div class="modal-body">';
        $output .= '<div class="mb-3">';
        $output .= '<label for="title" class="form-label">Title</label>';
        $output .= '<input type="text" class="form-control" id="title" name="title" value="'.$this->getTitle().'">';
        $output .= '</div>';
        $output .= '<div class="mb-3">';
        $output .= '<label for="description" class="form-label">Description</label>';
        $output .= '<input type="text" class="form-control" id="description" name="description" value="'.$this->getDescription().'">';
        $output .= '</div>';
        $output .= '<div class="mb-3 row">';
        $output .= '<div class="col">';
        $output .= '<label for="date" class="form-label">Date</label>';
        $output .= '<input type="date" class="form-control" id="date" name="date" value="'.date("Y-m-d", $this->getDate()).'">';
        $output .= '</div>';
        $output .= '<div class="col">';
        $output .= '<label for="time" class="form-label">Time</label>';
        $output .= '<input type="time" class="form-control" id="time" name="time" value="'.date("H:i", $this->getDate()).'">';
        $output .= '</div></div>';
        $output .= '<div class="mb-3 row">';
        $output .= '<div class="col">';
        $output .= '<label for="color" class="form-label">Choose a color</label>';
        $output .= '<input type="color" class="form-control form-control-color: w-100 p-0" id="color" name="color" value="'.$this->getColor().'">';
        $output .= '</div>';
        $output .= '<div class="col">';
        $output .= '<label for="dis" class="form-label">Temp</label>';
        $output .= '<input type="file" class="form-control" id="dis" name="dis">';
        $output .= '</div></div></div>';
        $output .= '<div class="modal-footer">';
        $output .= '<input type="hidden" name="id" value="'.$this->getId().'">';
        $output .= '<input type="hidden" name="returnUrl" value="'.htmlspecialchars($_SERVER['REQUEST_URI']).'">';
        $output .= '<button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#'.$this->getId().'ShareModal">Share</button>';
        $output .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
        $output .= '<button type="submit" class="btn btn-primary">Save changes</button>';
        $output .= '<button type="submit" name="delete" class="btn btn-danger">Delete</button>';
        return $output . '</div></form></div></div></div>' . $this->generateShareModal();
    }

    private function generateShareModal(): string
    {
        $output = '<div class="modal fade" id="'.$this->getId().'ShareModal" tabindex="-2" aria-labelledby="'.$this->getId().'ShareModalLabel" aria-hidden="true">';
        $output .= '<div class="modal-dialog modal-dialog-centered">';
        $output .= '<div class="modal-content text-start">';
        $output .= '<div class="modal-header">';
        $output .= '<h1 class="modal-title fs-5" id="'.$this->getId().'ShareModalLabel">Share event</h1>';
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $output .= '</div>';
        $output .= '<div class="modal-body">';
        $output .= '<div class="mb-3">';
        $output .= '<label for="readonlyInput" class="form-label">Link</label>';
        $output .= '<input type="text" class="form-control" id="readonlyInput" value="'.$_SERVER["SERVER_NAME"].'/index.php?page=shareEvent&origin='.htmlspecialchars($_SESSION["currentUser"]->getEmail()).'&event='.$this->getId().'" readonly>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="modal-footer">';
        $output .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
        return $output . '</div></div></div></div>';
    }
}