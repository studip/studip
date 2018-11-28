<?php

namespace FilesSearch;

/**
 * Simple class to hold everything about files search's filtering.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class Filter
{
    protected $category;
    protected $semester;

    /**
     * Returns all filter categories.
     *
     * @return array an associative array  containing `id` => `label` pairs
     */
    public static function getCategories()
    {
        return [
            'course' => _('Veranstaltungen'),
            'institute' => _('Einrichtungen'),
            'message' => _('Nachrichten'),
            'user' => _('Nutzer'),
        ];
    }

    /**
     * Is this filter really filtering?
     *
     * @return bool true if filtering, false otherwise
     */
    public function isFiltering()
    {
        return $this->hasCategory() || $this->hasSemester();
    }

    /**
     *  Return the active filter category.
     *
     * @return string the active filter category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the active filter category.
     *
     * @param string $category the active filter category
     *
     * @return Filter return `$this` for chaining
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Is a an active filter category set?
     *
     * @return bool true if there is an active filter category;
     *              false otherwise
     */
    public function hasCategory()
    {
        return $this->getCategory() != '';
    }

    /**
     *  Return the active filter semester.
     *
     * @return Semester the active filter semester
     */
    public function getSemester()
    {
        return $this->semester;
    }

    /**
     * Set the active filter semester.
     *
     * @param Semester $semester the active filter semester
     *
     * @return Filter return `$this` for chaining
     */
    public function setSemester(\Semester $semester = null)
    {
        $this->semester = $semester;

        return $this;
    }

    /**
     * Is a an active filter semester set?
     *
     * @return bool true if there is an active filter semester;
     *              false otherwise
     */
    public function hasSemester()
    {
        return $this->getSemester() !== null;
    }

    /**
     * Validate the filter.
     *
     * @return bool true if this filter is valid, false otherwise
     */
    public function validate()
    {
        if (isset($this->category)
            && !in_array($this->category, ['', 'course', 'institute', 'user', 'message'])) {
            return false;
        }

        return true;
    }

    /**
     * Returns a representation of this filter as an array.
     *
     * @return array the representation of this filter
     */
    public function toArray()
    {
        return [
            'category' => $this->category ?: '',
            'semester' => $this->semester ? $this->semester->id : '',
        ];
    }
}
