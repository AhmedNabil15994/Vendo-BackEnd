@if (is_rtl() == 'rtl')
    <script src="/admin/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-rtl.min.js" type="text/javascript">
    </script>
@else
    <script src="/admin/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript">
    </script>
@endif

<script src="/vendor/laravel-filemanager/js/single-stand-alone-button.js"></script>


<script>
    $(document).ready(function() {
        $('#clickmewow').click(function() {
            $('#radio1003').attr('checked', 'checked');
        });
    })
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $(".emojioneArea").emojioneArea();
    });
</script>

<style>
    .emojionearea .emojionearea-picker.emojionearea-picker-position-top {
        margin-bottom: -286px !important;
        right: -14px;
        z-index: 90000000000000;
    }

    .emojionearea .emojionearea-button.active+.emojionearea-picker-position-top {
        margin-top: 0px !important;
    }
</style>

<script>
    // DELETE ROW FROM DATATABLE
    function deleteRow(url) {
        var _token = $('input[name=_token]').val();

        bootbox.confirm({
            message: '{{ __('apps::vendor.general.delete_message') }}',
            buttons: {
                confirm: {
                    label: '{{ __('apps::vendor.general.yes_btn') }}',
                    className: 'btn-success'
                },
                cancel: {
                    label: '{{ __('apps::vendor.general.no_btn') }}',
                    className: 'btn-danger'
                }
            },

            callback: function(result) {
                if (result) {

                    $.ajax({
                        method: 'DELETE',
                        url: url,
                        data: {
                            _token: _token
                        },
                        success: function(msg) {
                            toastr["success"](msg[1]);
                            $('#dataTable').DataTable().ajax.reload();
                        },
                        error: function(msg) {
                            toastr["error"](msg[1]);
                            $('#dataTable').DataTable().ajax.reload();
                        }
                    });

                }
            }
        });
    }

    // DELETE ROW FROM DATATABLE
    function deleteAllChecked(url) {
        var someObj = {};
        someObj.fruitsGranted = [];

        $("input:checkbox").each(function() {
            var $this = $(this);

            if ($this.is(":checked")) {
                someObj.fruitsGranted.push($this.attr("value"));
            }
        });

        var ids = someObj.fruitsGranted;

        bootbox.confirm({
            message: '{{ __('apps::vendor.general.deleteAll_message') }}',
            buttons: {
                confirm: {
                    label: '{{ __('apps::vendor.general.delete_yes_btn') }}',
                    className: 'btn-success'
                },
                cancel: {
                    label: '{{ __('apps::vendor.general.delete_no_btn') }}',
                    className: 'btn-danger'
                }
            },

            callback: function(result) {
                if (result) {

                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {
                            ids: ids,
                        },
                        success: function(msg) {

                            if (msg[0] == true) {
                                toastr["success"](msg[1]);
                                $('#dataTable').DataTable().ajax.reload();
                            } else {
                                toastr["error"](msg[1]);
                            }

                        },
                        error: function(msg) {
                            toastr["error"](msg[1]);
                            $('#dataTable').DataTable().ajax.reload();
                        }
                    });

                }
            }
        });
    }

    $(document).ready(function() {

        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            if (start.isValid() && end.isValid()) {
                $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                $('input[name="from"]').val(start.format('YYYY-MM-DD'));
                $('input[name="to"]').val(end.format('YYYY-MM-DD'));
            } else {
                $('#reportrange .form-control').val('Without Dates');
                $('input[name="from"]').val('');
                $('input[name="to"]').val('');
            }
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                '{{ __('apps::vendor.general.date_range.today') }}': [moment(), moment()],
                '{{ __('apps::vendor.general.date_range.yesterday') }}': [moment().subtract(1, 'days'),
                    moment().subtract(1, 'days')
                ],
                '{{ __('apps::vendor.general.date_range.7days') }}': [moment().subtract(6, 'days'),
                    moment()
                ],
                '{{ __('apps::vendor.general.date_range.30days') }}': [moment().subtract(29, 'days'),
                    moment()
                ],
                '{{ __('apps::vendor.general.date_range.month') }}': [moment().startOf('month'),
                    moment().endOf('month')
                ],
                '{{ __('apps::vendor.general.date_range.last_month') }}': [moment().subtract(1,
                    'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            },
            @if (is_rtl() == 'rtl')
                opens: 'left',
            @endif
            buttonClasses: ['btn'],
            applyClass: 'btn-primary',
            cancelClass: 'btn-danger',
            format: 'YYYY-MM-DD',
            separator: 'to',
            locale: {
                applyLabel: '{{ __('apps::vendor.general.date_range.save') }}',
                cancelLabel: '{{ __('apps::vendor.general.date_range.cancel') }}',
                fromLabel: 'from',
                toLabel: 'to',
                customRangeLabel: '{{ __('apps::vendor.general.date_range.custom') }}',
                firstDay: 1
            }
        }, cb);

        cb(start, end);

    });
</script>

<script>
    $('.lfm').filemanager('image');

    $('.delete').click(function() {
        $(this).closest('.form-group').find($('.' + $(this).data('input'))).val('');
        $(this).closest('.form-group').find($('.' + $(this).data('preview'))).html('');
    });
</script>


<script src="https://js.pusher.com/5.0/pusher.min.js"></script>

<script>
    var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
        cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
        forceTLS: true
    });


    pusher.subscribe('{{ config('core.config.constants.VENDOR_DASHBOARD_CHANNEL') }}').bind(
        '{{ config('core.config.constants.VENDOR_DASHBOARD_ACTIVITY_LOG') }}',
        function(data) {

            $('#dataTable').DataTable().ajax.reload();

            if (jQuery.inArray('{{ auth()->id() }}', data.activity.ids)) {
                openActivity(data.activity);
            }

        });

    function playSound() {
        var audio = new Audio('{{ url('uploads/media/doorbell-5.mp3') }}');
        audio.play();
    }

    function openActivity(response) {
        playSound();
        // Show
        var showUrl = response.url;

        swal({
                title: response.description_{{ locale() }},
                icon: "success",
                buttons: true,
                dangerMode: true,
            })
            .then((willDone) => {
                if (willDone) {
                    window.location.href = showUrl;
                }
            });
    }

    $("#showAllDeleted").bootstrapSwitch({
        onSwitchChange: function(e, state) {
            var data = {};
            if (state)
                data['deleted'] = 'only';

            $('#dataTable').DataTable().destroy();
            tableGenerate(data);
        }
    });

    function toggleBooleanSwitch(el, toggle_show_element) {
        var checked = $(el).is(':checked');
        if (checked) {
            $(toggle_show_element).show();
        } else {

            $(toggle_show_element).hide();
        }
    }
</script>

<script>
    $(document).ready(function() {

        $("#select2Vendor").select2({
            placeholder: "{{ __('catalog::vendor.addon_categories.alert.select_vendor') }}",
            allowClear: true,
        });

        $('#select2AddonCategory').select2({
            placeholder: "{{ __('catalog::vendor.addon_options.alert.select_addon_category') }}",
            allowClear: true,
        });

        $('#select2Vendor').on('select2:select', function(e) {
            var data = e.params.data;
            getAddonCategoriesByVendor(data.id);
        });

        $('#select2Vendor').on('select2:unselect', function(e) {
            getAddonCategoriesByVendor(null);
        });

    });

    function getAddonCategoriesByVendor(vendorId) {
        let data = {
            'vendor_id': vendorId,
        };

        $.ajax({
            method: "GET",
            url: "{{ route('vendor.addon_options.get_addon_categories_by_vendor') }}",
            data: data,
            beforeSend: function() {
                $('#select2AddonCategorySection').hide();
            },
            success: function(data) {},
            error: function(data) {
                var getJSON = $.parseJSON(data.responseText);
            },
            complete: function(data) {
                var getJSON = $.parseJSON(data.responseText);
                buildAddonCategoriesDropdown(getJSON.data);
            },
        });
    }

    function buildAddonCategoriesDropdown(data) {
        var label = "--- {{ __('catalog::vendor.addon_options.alert.select_addon_category') }} ---";
        var row = `<option value="">${label}</option>`;
        $.each(data, function(i, value) {
            row += `<option value="${value.id}">${value.title}</option>`;
        });
        $('#select2AddonCategory').html(row);
        $('#select2AddonCategorySection').show();
    }
</script>
