Showing stats from {COUNT} months {CSV_BUTTON}
<table class="faxmaster_stats">
    <thead>
        <tr>
            <th class="col1out">Month</th>
            <th class="col2out">Year</th>
            <th class="col3out">Faxes</th>
            <th class="col4out">Pages</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4">
                <div class="innerBox">
                    <table class="faxmaster_stats_inner">
                        <!-- BEGIN empty_table -->
                        <tr><td colspan="4">{EMPTY_MESSAGE}</td></tr>
                        <!-- END empty_table -->
                        <!-- BEGIN repeat_row -->
                        <tr {TOGGLE}>
                            <td class="col1in">{month}</td>
                            <td class="col2in">{year}</td>
                            <td class="col3in">{numFaxes}</td>
                            <td class="col4in">{numPages}</td>
                        </tr>
                        <!-- END repeat_row -->
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>
