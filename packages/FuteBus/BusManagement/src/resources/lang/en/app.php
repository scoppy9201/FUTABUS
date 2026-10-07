<?php

return [
    // Page
    'vehicle_types_title'       => 'Vehicle Type Management',
    'vehicle_types_subtitle'    => 'View, add, edit and deactivate vehicle types used as the basis for assigning trips.',

    // Table columns
    'col_stt'                   => '#',
    'col_name'                  => 'Vehicle Type Name',
    'col_description'           => 'Description',
    'col_capacity'              => 'Default Capacity',
    'col_status'                => 'Status',
    'col_actions'               => 'Actions',

    // Status
    'status_active'             => 'In use',
    'status_inactive'           => 'Deactivated',

    // Toolbar
    'search_placeholder'        => 'Search by vehicle type name...',
    'btn_search'                => 'Search',
    'btn_clear_filter'          => 'Clear filter',
    'btn_add'                   => 'Add vehicle type',
    'btn_edit'                  => 'Edit',
    'btn_delete'                => 'Delete',
    'btn_cancel'                => 'Cancel',
    'btn_save'                  => 'Save changes',
    'btn_confirm_delete'        => 'Confirm delete',
    'btn_add_now'               => '+ Add one now',

    // Empty
    'empty'                     => 'No vehicle types found.',

    // Add modal
    'modal_add_title'           => 'Add vehicle type',
    'field_name'                => 'Vehicle type name',
    'field_name_placeholder'    => 'E.g. Sleeper 40 seats',
    'field_name_hint'           => 'The vehicle type name must be unique across the system.',
    'field_description'         => 'Description',
    'field_description_placeholder' => 'Short description of this vehicle type...',
    'field_capacity'            => 'Default capacity',
    'field_capacity_unit'       => 'seats',
    'field_required'            => '*',
    'btn_store'                 => 'Add',

    // Edit modal
    'modal_edit_title'          => 'Update vehicle type',

    // Delete modal
    'modal_delete_title'        => 'Confirm delete vehicle type',
    'modal_delete_message'      => 'Are you sure you want to delete vehicle type',
    'modal_delete_note_title'   => '⚠ Business rule note:',
    'modal_delete_note_1'       => 'If this type is assigned to a vehicle, it will be deactivated instead of permanently deleted.',
    'modal_delete_note_2'       => 'If not assigned, it will be permanently removed from the system.',

    // Flash messages (used in controller)
    'flash_created'             => 'Vehicle type added successfully.',
    'flash_updated'             => 'Vehicle type updated successfully.',
    'flash_deleted'             => 'Vehicle type deleted successfully.',
    'flash_deactivated'         => 'Vehicle type is in use and has been deactivated.',

    'bus_title'            => 'Bus Information',
'bus_subtitle'         => 'Search, add, edit and deactivate individual buses.',
'bus_search_ph'        => 'Search by plate, chassis, brand...',
'bus_f_plate'          => 'License plate',
'bus_f_chassis'        => 'Chassis number',
'bus_f_type'           => 'Vehicle type',
'bus_f_type_select'    => '-- Select vehicle type --',
'bus_f_brand'          => 'Brand',
'bus_f_color'          => 'Color',
'bus_f_year'           => 'Year of manufacture',
'bus_f_rows'           => 'Seat rows',
'bus_f_cols'           => 'Seat columns',
'bus_modal_add'        => 'Add bus',
'bus_modal_edit'       => 'Update bus information',
'bus_modal_delete'     => 'Confirm deactivate bus',
'bus_delete_msg'       => 'Are you sure you want to deactivate bus',
'bus_flash_created'    => 'Bus added successfully.',
'bus_flash_updated'    => 'Bus updated successfully.',
'bus_flash_deactivated'=> 'Bus has been deactivated.',
'bus_err_active_trip'  => 'This bus is assigned to an active trip and cannot be edited or deleted.',
];
