<?php 

 $stat 	= $data['stat_estimates'];
 $total = $data['stat_total_estimates'];
?>

    <div class="row">

        <div class="col-md-9">
            <div class="row">
                <div class="col-md-3">
                    <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                        {{ format_currency($stat[ESTIMATE_STATUS_DRAFT]['total'] , TRUE) }}
                        <div class="text-secondary">@lang('form.draft')</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                        {{ format_currency($stat[ESTIMATE_STATUS_SENT]['total'] , TRUE) }}
                        <div class="text-primary">@lang('form.sent')</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                        {{ format_currency($stat[ESTIMATE_STATUS_EXPIRED]['total'] , TRUE) }}
                        <div class="text-warning">@lang('form.expired')</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                        {{ format_currency($stat[ESTIMATE_STATUS_DECLINED]['total'] , TRUE) }}
                        <div class="text-danger">@lang('form.declined')</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                {{ format_currency($stat[ESTIMATE_STATUS_ACCEPTED]['total'] , TRUE) }}
                <div class="text-success">@lang('form.accepted')</div>
            </div>
        </div>

    </div>

    <div class="row" style="font-size: 13px;">

        <div class="col-md-9">
            <div class="row">
                <div class="col-md-3">
                    @lang('form.draft') : {{ $stat[ESTIMATE_STATUS_DRAFT]['number'] }} / {{ $total }}
                    <?php gen_progress_bar('bg-secondary', $stat[ESTIMATE_STATUS_DRAFT]['percent']) ;?>
                </div>

                <div class="col-md-3">
                    @lang('form.sent') : {{ $stat[ESTIMATE_STATUS_SENT]['number'] }} / {{ $total }}
                    <?php gen_progress_bar('bg-primary', $stat[ESTIMATE_STATUS_SENT]['percent'] ) ;?>
                </div>

                <div class="col-md-3">
                    @lang('form.expired') : {{ $stat[ESTIMATE_STATUS_EXPIRED]['number'] }} / {{ $total }}
                    <?php gen_progress_bar('bg-warning', $stat[ESTIMATE_STATUS_EXPIRED]['percent'] ) ;?>
                </div>

                <div class="col-md-3">
                    @lang('form.declined') : {{ $stat[ESTIMATE_STATUS_DECLINED]['number'] }} / {{ $total }}
                    <?php gen_progress_bar('bg-danger', $stat[ESTIMATE_STATUS_DECLINED]['percent'] ) ;?>
                </div>

            </div>
        </div>

        <div class="col-md-3">
            @lang('form.accepted') : {{ $stat[ESTIMATE_STATUS_ACCEPTED]['number'] }} / {{ $total }}
            <?php gen_progress_bar('bg-success', $stat[ESTIMATE_STATUS_ACCEPTED]['percent'] ) ;?>
        </div>

    </div>

    <br>
