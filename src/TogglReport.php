<?php

class TogglReport {
    
    public static function loadDateRange(TogglReportConnection $connection, $workspaceId, $start_date, $end_date, array $options = array()) {
        if (!$start_date || !$end_date) {
            throw new TogglException("Invalid parameters for loading report.");
        }
        
        $ret = array(
            'data' => array(),
            'count' => 0
        );
        
        if (isset($start_date) && isset($end_date)) {
            
            if ($end_date < $start_date) {
                throw new TogglException("Start date cannot be after the end date.");
            }
            $options['query']['workspace_id'] = $workspaceId;
            $options['query']['since'] = gmdate('Y-m-d', $start_date);
            $options['query']['until'] = gmdate('Y-m-d', $end_date);
        } else {
            return $ret;
        }
        
        $page = 1;
        while (true) {
          $options['query']['page'] = $page;
          $response = $connection->request('details', $options);
          if (!$response->success) throw new TogglException($response->data['error']['message']);
          
          foreach ($response->data['data'] as $key => $record) {
              $entry = new TogglTimeEntry($connection, $record);
              $entry->wid = $workspaceId;
              $ret['data'][] = $entry;
          }
          if ($page==$response->data['total_count']) break;
          $page++;
        }
        $ret['count'] = count($ret['data']);
        return $ret;
    }
    
}
