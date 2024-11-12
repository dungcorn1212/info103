<?php

use app\services\AbstractKanban;

class ApprovifyRequestsKanBan extends AbstractKanban
{
    protected function table()
    {
        return 'approvify_requests';
    }

    public function defaultSortDirection()
    {
        return 'desc';
    }

    public function defaultSortColumn()
    {
        return 'id';
    }

    public function limit()
    {
        return 50;
    }

    protected function applySearchQuery($q)
    {
        $searchTerm = $this->ci->db->escape_str(strafter($q, '#'));
        $this->ci->db->where(db_prefix() . 'approvify_requests.request_title LIKE "%'.$searchTerm.'%"');

        return $this;
    }

    protected function initiateQuery()
    {

        $this->ci->db->select(db_prefix() . 'approvify_requests.id,' . db_prefix() . 'approvify_requests.request_title,' . db_prefix() . 'approvify_requests.status,' . db_prefix() . 'approvify_requests.created_at,' . db_prefix() . 'approvify_requests.requester_id,' . db_prefix() . 'approvify_requests.category_id,' . db_prefix() . 'approvify_approval_categories.category_name,' . db_prefix() . 'approvify_approval_categories.category_icon');
        $this->ci->db->from(db_prefix() . 'approvify_requests');
        $this->ci->db->join(db_prefix() . 'approvify_approval_categories', db_prefix() . 'approvify_approval_categories.id = ' . db_prefix() . 'approvify_requests.category_id');

        $this->ci->db->where(db_prefix() . 'approvify_requests.requester_id=' . get_staff_user_id());

        return $this;
    }
}
