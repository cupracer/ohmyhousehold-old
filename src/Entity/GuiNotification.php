<?php

namespace App\Entity;

class GuiNotification
{
    /** @var string */
    private $category;

    /** @var string */
    private $title;

    /** @var string */
    private $note;

    /** @var int */
    private $itemId;

    /** @var string */
    private $cssClass;

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return GuiNotification
     */
    public function setCategory(string $category): GuiNotification
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return GuiNotification
     */
    public function setTitle(string $title): GuiNotification
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     * @return GuiNotification
     */
    public function setNote(string $note): GuiNotification
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     * @return GuiNotification
     */
    public function setItemId(int $itemId): GuiNotification
    {
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    /**
     * @param string $cssClass
     * @return GuiNotification
     */
    public function setCssClass(string $cssClass): GuiNotification
    {
        $this->cssClass = $cssClass;
        return $this;
    }
}