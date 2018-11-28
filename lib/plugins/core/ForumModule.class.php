<?php
/**
 * ForumModule.class.php - Interface for all intersections between the Stud.IP 
 *                         Core and something that behaves like a forum
 *
 * Implement all interface methods and you can integrate your plugin like
 * a real core-module into Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

interface ForumModule extends StandardPlugin
{
    /**
     * Issues can be connected with an entry in a forum. This method
     * has to return an url to the connected topic for the passed issue_id.
     * If no topic is connected, it has to return "false"
     * 
     * @param string $issue_id 
     * @return mixed URL or false
     */
    function getLinkToThread($issue_id);
    
    /**
     * This method is called in case of an creation OR an update of an issue.
     * Normally one would update the title and the content of the linked topic
     * when called
     * 
     * @param string $issue_id
     * @param string $title     the title of the issue
     * @param string $content   the description of the issue
     */
    function setThreadForIssue($issue_id, $title, $content);
    
    /**
     * Return the number of postings the connected topic contains for
     * the issue with the passed id
     * 
     * @param type $issue_id
     * 
     * @return int
     */
    function getNumberOfPostingsForIssue($issue_id);
    
    /**
     * Return the number of postings for the passed user
     * 
     * @param type $user_id
     * 
     * @return int
     */    
    function getNumberOfPostingsForUser($user_id);
    
    /**
     * Return the number of postings for the passed seminar
     * 
     * @param type $seminar_id
     * 
     * @return int
     */        
    function getNumberOfPostingsForSeminar($seminar_id);
    
    /**
     * Return the number of all postings served by your module. The
     * results are used for statistics.
     * 
     * @return int
     */        
    function getNumberOfPostings();

    /**
     * This function is called whenever Stud.IP needs to directly operate
     * on your entries-table. Your entries-table MUST have at least fields
     * for a date (a change-date is preferred, but make-date will suffice),
     * posting-content, seminar_id and user_id.
     * 
     * The returning array must have the following structure:
     * Array (
     *     'table'      => 'your_entry_table,
     *     'content'    => 'your_content_field',
     *     'chdate'     => 'your_date_field',
     *     'seminar_id' => 'your_seminar_id_field',
     *     'user_id'    => 'your_user_id_field'
     * )
     * 
     * @return array
     */
    function getEntryTableInfo();
    
    /**
     * The caller expects an array of the ten seminars with the most postings
     * in your module.
     * 
     * Return an array of the following structure:
     * Array (
     *     Array (
     *         'seminar_id' =>
     *         'display'    =>
     *         'count'      =>
     *     )
     * )
     * 
     * @return array
     */
    function getTopTenSeminars();
    
    /**
     * Is called when the data of a user is moved to another user.
     * Update all user_ids with the passed new one.
     * 
     * @param string $user_from  the user_id of the user who has the data
     * @param string $user_to    the user_id of the user who shall receive the data
     */
    function migrateUser($user_from, $user_to);
    
    /**
     * Clean up everything for the passed seminar, because the seminar
     * is beeing deleted.
     * 
     * @param string $seminar_id
     */
    function deleteContents($seminar_id);
    
    /**
     * Return a complete HTML-Dump of all entries in the forum-module. This is 
     * used for archiving purposes, so make it pretty!
     * 
     * @param string $seminar_id
     * 
     * @return string  a single-page HTML-view of all contents in one string
     */
    function getDump($seminar_id);
}