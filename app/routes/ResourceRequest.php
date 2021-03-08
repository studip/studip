<?php
namespace RESTAPI\Routes;

/**
 * This file contains the REST class for resource requests from the
 * room and resource management system.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class ResourceRequest extends \RESTAPI\RouteMap
{

    /**
     * Helper method that either returns the specified data
     * or simply an empty string in case that no request result
     * is requested.
     */
    protected function sendReturnData($data)
    {
        if (\Request::submitted('quiet')) {
            //Return nothing.
            return '';
        }

        //Return data.
        return $data;
    }


    /**
     * Moves a resource request, if permitted.
     *
     * @post /resources/request/:request_id/move
     */
    public function move($request_id)
    {
        $request = \ResourceRequest::find($request_id);
        if (!$request) {
            $this->notFound('Resource request object not found!');
        }

        $current_user = \User::findCurrent();

        if ($request->isReadOnlyForUser($current_user)) {
            throw new \AccessDeniedException();
        }

        $begin_str = \Request::get('begin');
        $end_str = \Request::get('end');

        //Try the ISO format first: YYYY-MM-DDTHH:MM:SS±ZZ:ZZ
        $begin = \DateTime::createFromFormat(\DateTime::RFC3339, $begin_str);
        $end = \DateTime::createFromFormat(\DateTime::RFC3339, $end_str);
        if (!($begin instanceof \DateTime) || !($end instanceof \DateTime)) {
            $tz = new \DateTime();
            $tz = $tz->getTimezone();
            $begin = \DateTime::createFromFormat('Y-m-d\TH:i:s', $begin_str, $tz);
            $end = \DateTime::createFromFormat('Y-m-d\TH:i:s', $end_str, $tz);
        }

        $request->begin = $begin->getTimestamp();
        $request->end = $end->getTimestamp();

        try {
            $request->store();
            return $this->sendReturnData($request->toRawArray());
        } catch (Exception $e) {
            $this->halt(500, $e->getMessage());
        }
    }


    /**
     * Changes the reply comment of a request.
     *
     * @post /resources/request/:request_id/edit_reply_comment
     */
    public function editReplyComment($request_id)
    {
        $request = \ResourceRequest::find($request_id);
        if (!$request) {
            $this->notFound('Resource request object not found!');
        }

        $current_user = \User::findCurrent();

        if ($request->isReadOnlyForUser($current_user)) {
            throw new \AccessDeniedException();
        }

        $quiet = \Request::get('quiet');
        $new_reply_comment = \Request::get('reply_comment');

        $request->reply_comment = $new_reply_comment;

        if ($request->isDirty()) {
            try {
                $request->store();
                if ($this->quiet) {
                    return '';
                } else {
                    return $this->sendReturnData($request->toRawArray());
                }
            } catch (Exception $e) {
                $this->halt(500, $e->getMessage());
            }
        } else {
            if ($this->quiet) {
                return '';
            } else {
                return $this->sendReturnData($request->toRawArray());
            }
        }
    }


    /**
     * Changes the reply comment of a request.
     *
     * @post /resources/request/:request_id/toggle_marked
     */
    public function toggleMarkedFlag($request_id)
    {
        $request = \ResourceRequest::find($request_id);
        if (!$request) {
            $this->notFound('Resource request object not found!');
        }

        $current_user = \User::findCurrent();

        if ($request->isReadOnlyForUser($current_user)) {
            throw new \AccessDeniedException();
        }

        //Switch to the next marking state or return to the unmarked state
        //if the next marking state would be after the last defined
        //marking state.
        $request->marked = (++$request->marked % \ResourceRequest::MARKING_STATES);

        if ($request->isDirty()) {
            $request->store();
        }

        return $request;
    }
}
