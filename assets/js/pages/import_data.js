App.Pages.ImportData = (function () {
    const $SystemList = $('#import-systems');
    const $DetailsList = $('.import-system-details');
    const $SystemsTitle = $('h4.import-data-title');

    const importSystems = vars('import_systems') || [];

    let selectedSystem = null;

    function getSystemById(id) {
        return importSystems.find((s) => { return s.id == id; });
    }

    function doUpload(field) {
        if (!selectedSystem) return;

        const $upl = $('#file-' + field);
        const file = $upl[0].files[0];

        if (file) {
            App.Utils.File.toBase64(file).then((base64) => {
                // const crypto = window.crypto;
                // crypto.subtle.encrypt()
                const data = {
                    'fileBase64': base64,
                    'filename': file.name,
                    'system': selectedSystem.id,
                    'type': field
                };

                App.Http.ImportData.upload(selectedSystem, field, data).done(() => {
                    App.Layouts.Backend.displayNotification(lang('file_uploaded'));
                })
            })
            
        } else {
            App.Layouts.Backend.displayNotification(lang('no_file_selected'));
        }
    }

    function updateTitle() {
        let title = lang('import_data');

        if (selectedSystem) {
            title += ': ' + selectedSystem.name;
        }

        $SystemsTitle.text(title);
    }

    function showUploadFields(system) {
        const $datasets = $DetailsList.find('.datasets');
        const fieldTypes = system.data;

        $datasets.empty();

        fieldTypes.forEach((type) => {
            
            $('<div/>', {
                'class': 'row',
                'html': [
                    $('<div/>', {
                        'class': 'col-12',
                        'html': [
                            $('<div/>', {
                                'class': 'mb-3',
                                'html': [
                                    $('<label/>', {
                                        'text': lang('import_type_' + type),
                                        'class': 'form-label'
                                    }),
                                    $('<div/>', {
                                        'class': 'input-group',
                                        'html': [
                                            $('<input/>', {
                                                'id': 'file-' + type,
                                                'type': 'file',
                                                'data-field': 'file_' + type,
                                                'class': 'form-control',
                                                'change': () => {
                                                    $('#upload_' + type).removeClass('disabled');
                                                }
                                            }),
                                            $('<button/>', {
                                                'type': 'button',
                                                'id': 'upload_' + type,
                                                'class': 'btn btn-outline-primary disabled',
                                                'html': [
                                                    $('<i/>' ,{
                                                        'class': 'fas fa-arrow-up-from-bracket me-2',
                                                    }), 
                                                    'upload'
                                                ],
                                                'click': () => {
                                                    doUpload(type);
                                                }
                                            }), 
                                        ]
                                    })
                                    
                                ]
                            })
                        ]
                    })
                ]
            }).appendTo($datasets);
        });

    }

    function fillSystems() {
        const $systems = $SystemList.find('.systems');

        if (Array.isArray(importSystems)) {
            importSystems.forEach(system => {
                if (Array.isArray(system.data)) {
                    dataTypes = system.data.map((entry) => {
                        return lang('import_type_' + entry);
                    }).join(', ');
                } else {
                    dataTypes = '';
                }
                
                $('<div/>', {
                    'class': 'system entry',
                    'data-id': system.id,
                    'html': [
                        $('<strong/>', {
                            'text': system.name,
                        }),
                        $('<br/>'),
                        $('<small/>', {
                            'class': 'text-muted',
                            'text': dataTypes,
                        }),
                        $('<br/>'),
                    ],
                }).appendTo($systems);

                $systems.append($('<hr/>'));
            });

            $SystemList.find('.system').first().trigger('click');
        }
    }

    function addEventListeners() {
        $SystemList.on('click', '.system', (event) => {

            const systemId = $(event.currentTarget).attr('data-id');

            selectedSystem = getSystemById(systemId);

            $SystemList.find('.selected').removeClass('selected');
            $(event.currentTarget).addClass('selected');

            updateTitle();

            App.Pages.ImportData.showUploadFields(selectedSystem);
        });
    }

    function initialize() {
        App.Pages.ImportData.addEventListeners();
        App.Pages.ImportData.fillSystems();
    }

    document.addEventListener('DOMContentLoaded', initialize);

    return {
        fillSystems,
        showUploadFields,
        addEventListeners
    }

})();