App.Utils.Calendar = (function() {
    const $reloadAppointments = $('#reload-appointments');

    function deleteAppointment(appointment) {
        if (Number.isInteger(Number(appointment))) {
            const appointmentId = Number(appointment);
            App.Http.Appointments.find(appointmentId).done((result) => {
                appointment = result;
                deleteAppointment(appointment);
            });
            return;
        }

        if (appointment.workingPlanException !== undefined) {
                const providerId = $selectFilterItem.val();

                const provider = vars('available_providers').find(
                    (availableProvider) => Number(availableProvider.id) === Number(providerId),
                );

                if (!provider) {
                    throw new Error('Provider could not be found: ' + providerId);
                }

                const successCallback = () => {
                    App.Layouts.Backend.displayNotification(lang('working_plan_exception_deleted'));

                    const workingPlanExceptions = JSON.parse(provider.settings.working_plan_exceptions) || {};
                    delete workingPlanExceptions[date];

                    for (const index in vars('available_providers')) {
                        const availableProvider = vars('available_providers')[index];

                        if (Number(availableProvider.id) === Number(providerId)) {
                            availableProvider.settings.working_plan_exceptions = JSON.stringify(workingPlanExceptions);
                            break;
                        }
                    }

                    $reloadAppointments.trigger('click'); // Update the calendar.
                };

                const date = moment(lastFocusedEventData.start).format('YYYY-MM-DD');

                App.Http.Calendar.deleteWorkingPlanException(date, providerId, successCallback);
            } else if (!appointment.is_unavailability) {
                const buttons = [
                    {
                        text: lang('cancel'),
                        click: (event, messageModal) => {
                            messageModal.hide();
                        },
                    },
                    {
                        text: lang('delete'),
                        click: (event, messageModal) => {
                            const appointmentId = appointment.id;

                            const cancellationReason = $('#cancellation-reason').val();

                            App.Http.Calendar.deleteAppointment(appointmentId, cancellationReason).done(() => {
                                messageModal.hide();

                                // Refresh calendar event items.
                                $reloadAppointments.trigger('click');
                            });
                        },
                    },
                ];

                App.Utils.Message.show(
                    lang('delete_appointment_title'),
                    lang('write_appointment_removal_reason'),
                    buttons,
                );

                $('<textarea/>', {
                    'class': 'form-control w-100',
                    'id': 'cancellation-reason',
                    'rows': '3',
                }).appendTo('#message-modal .modal-body');
            } else {
                // Do not display confirmation prompt.

                const unavailabilityId = appointment.id;

                App.Http.Calendar.deleteUnavailability(unavailabilityId).done(() => {
                    // Refresh calendar event items.
                    if ($reloadAppointments) {
                        $reloadAppointments.trigger('click');
                    }
                });
            }
    }

    return {
        deleteAppointment,
    };
})();