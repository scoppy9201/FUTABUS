<?php

return [
    'col_stt' => 'No.',
    'col_status' => 'Status',
    'col_actions' => 'Actions',

    'status_active' => 'Active',
    'status_inactive' => 'Inactive',

    'btn_search' => 'Search',
    'btn_clear_filter' => 'Clear filters',
    'btn_add' => 'Add schedule',
    'btn_edit' => 'Edit',
    'btn_delete' => 'Deactivate',
    'btn_cancel' => 'Cancel',
    'btn_save' => 'Save changes',
    'btn_store' => 'Add new',
    'btn_confirm' => 'Confirm',

    'empty' => 'No schedules available.',

    'sch_title' => 'Vehicle Schedule Management',
    'sch_subtitle' => 'Set up recurring routes; the system automatically generates trips for the next 30 days.',
    'sch_search_ph' => 'Search by route code, departure, destination...',

    'sch_f_route' => 'Route',
    'sch_f_route_select' => '-- Select route --',
    'sch_f_time' => 'Departure time',
    'sch_f_duration' => 'Travel time (minutes)',
    'sch_f_days' => 'Days of the week',
    'sch_f_from' => 'Effective from',
    'sch_f_to' => 'Effective until',
    'sch_f_price' => 'Ticket price (₫)',

    'sch_modal_add' => 'Add schedule',
    'sch_modal_edit' => 'Update schedule',
    'sch_modal_delete' => 'Confirm schedule deactivation',

    'sch_delete_msg' => 'Deactivate the schedule for route',
    'sch_delete_note' => 'The system will no longer generate new trips. Previously generated trips will be kept unchanged.',

    'sch_flash_created' => 'Schedule added successfully, corresponding trips have been generated.',
    'sch_flash_updated' => 'Schedule updated successfully.',
    'sch_flash_deactivated' => 'Schedule has been deactivated. Previously generated trips are kept unchanged.',

    'sch_err_date' => 'The start date must be less than or equal to the end date.',
    'sch_err_conflict' => 'A schedule with the same route, time slot, and effective dates already exists (:from → :to).',
    'sch_err_inactive' => 'The schedule is no longer active.',
    'sch_err_generate' => 'Unable to update or generate trips. The data has been kept unchanged.',
    'sch_err_past' => 'The end date cannot be earlier than today.',

    'day_1' => 'Mon',
    'day_2' => 'Tue',
    'day_3' => 'Wed',
    'day_4' => 'Thu',
    'day_5' => 'Fri',
    'day_6' => 'Sat',
    'day_7' => 'Sun',

    'unassigned' => 'Vehicle not assigned',

    

    'tr_title' => 'Trip Management',
    'tr_subtitle' => 'Assign vehicles to trips generated from schedules, add one-off trips, edit fares, and cancel trips.',
    'tr_search_ph' => 'Search by route, license plate...',
    'tr_filter_all' => 'All statuses',
    'tr_empty' => 'No trips available.',

    'tr_f_departure' => 'Departure time',
    'tr_f_arrival' => 'Estimated arrival time',
    'tr_f_bus' => 'Vehicle',
    'tr_f_bus_select' => '-- No vehicle assigned --',
    'tr_f_price' => 'Base fare (₫)',

    'tr_col_seats' => 'Available seats',
    'tr_col_source' => 'Source',
    'tr_src_schedule' => 'Schedule',
    'tr_src_manual' => 'One-off trip',

    'tr_btn_add' => 'Add one-off trip',
    'tr_btn_edit' => 'Assign vehicle / Edit',
    'tr_btn_cancel' => 'Cancel trip',

    'tr_modal_add' => 'Add one-off trip',
    'tr_modal_edit' => 'Update trip',
    'tr_modal_cancel' => 'Confirm trip cancellation',

    'tr_cancel_msg' => 'Are you sure you want to cancel this trip',
    'tr_cancel_note_tickets' => 'tickets have been sold. The system will cancel the trip and initiate the refund / trip change process for passengers.',

    'tr_locked_note' => 'Trips generated from schedules: only vehicle assignment and fare editing are allowed. To change the route or departure time, cancel the trip.',

    'tr_flash_created' => 'Trip added successfully.',
    'tr_flash_updated' => 'Trip updated successfully.',
    'tr_flash_cancelled' => 'Trip cancelled successfully.',
    'tr_flash_cancelled_tickets' => 'Trip cancelled. :n sold tickets require a refund or trip change.',

    'tr_err_past' => 'Departure time must be later than the current time.',
    'tr_err_arrival' => 'Arrival time must be after the departure time.',
    'tr_err_price' => 'Base fare must be greater than 0.',
    'tr_err_conflict' => 'The vehicle is already assigned to another trip during the same time period. Please select another vehicle.',
    'tr_err_bus' => 'The vehicle does not exist or is no longer in use.',
    'tr_err_status' => 'Only trips that have not departed (unassigned or scheduled) can be edited or cancelled.',
    'tr_err_has_tickets' => 'The trip has sold tickets and cannot be edited. Please cancel the trip to process refunds or trip changes for passengers.',
    'tr_err_cancel_past' => 'The trip has already departed and cannot be cancelled.',
    'tr_err_cancel_48h' => 'Trips with sold tickets can only be cancelled at least 48 hours before departure.',

];