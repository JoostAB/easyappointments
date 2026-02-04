/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.6.0
 * ---------------------------------------------------------------------------- */

/**
 * Appointment status options component.
 *
 * This module implements the appointment status options.
 */
App.Components.BookingStatusses = (function () {
    
    const $listGroup = $('.booking_statusses_list');
    let deleted = [];

    function renderListGroupItem(bookingStatus) {
        return $(`
            <li class="list-group-item d-flex justify-content-between align-items-center p-0 border-0 mb-3 booking-status">
                <input class="display-none status-id" value="${bookingStatus?bookingStatus.id:null}">
                <label class="w-25 me-2">
                    <input class="form-control status-name" value="${bookingStatus?bookingStatus.name:''}">
                </label>
                <label class="w-75 me-2">
                    <input class="form-control status-description" value="${bookingStatus?bookingStatus.description:''}">
                </label>
                <div class="me-2">
                    <label >
                        <input class="form-check-input status-isbusy" type="checkbox" ${(!bookingStatus||bookingStatus.is_busy)?'checked':''}>
                    </label>
                </div>

                <button type="button" class="btn btn-outline-danger delete-booking-status">
                    <i class="fas fa-trash"></i>
                </button>
                
            </li>
        `);
    }

    function clearList() {
        if (!$listGroup.length) {
            return;
        }

        $listGroup.find('.list-group-item').remove();
    }

    function getOptions() {
        App.Http.BusinessSettings.getBookingStatusses().then((data) => {
            const stats = data;

            clearList();

            deleted = [];
        
            stats.forEach((stat) => {
                renderListGroupItem(stat).appendTo($listGroup);
            });
        });
    }

    function addStatus() {
        renderListGroupItem().appendTo($listGroup);
    }

    function deleteStatus(event) {
        const $li = $(event.currentTarget).closest('li');
        const id = $li.find('.status-id').val();
        if (id) {
            deleted.push(id);
        }
        $li.remove();
    }

    function initialize() {
        $('.add-booking-status').on('click', addStatus);
        $('.load-booking-status').on('click', getOptions);
        $(document).on('click', '.delete-booking-status', deleteStatus);
        getOptions();
    }

    function serialize() {
        if (!$listGroup.length) {
            return;
        }

        let statusses = [];

        const items = $listGroup.find('.list-group-item');
        items.each((idx, li) => {
            const $li = $(li);
            
            const stat = {
                id: $li.find('.status-id').val(),
                name: $li.find('.status-name').val(),
                description: $li.find('.status-description').val(),
                is_busy: $li.find('.status-isbusy').prop('checked'),
            };
            statusses.push(stat);
        });

        return statusses;
    }

    function getDeleted() {
        return deleted;
    }
    
    document.addEventListener('DOMContentLoaded', initialize);
    
    return {
        getOptions,
        serialize,
        getDeleted,
    };
})();