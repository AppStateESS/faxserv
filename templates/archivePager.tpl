<div align="right">
    {SEARCH}
</div>

<table class="table table-striped">
    <tr>
        <th>File Name</th>
        <th>Sender Phone</th>
        <th>Date Received{DATERECEIVED_SORT}</th>
        <th>Banner ID</th>
        <th>Name</th>
        <th>Pages</th>
        <th>Archived in:</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="3">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr id="{id}_pagerRow">
        <td>{fileName}</td>
        <td>{senderPhone}</td>
        <td>{dateReceived}</td>
        <td id="{id}_bannerid">{bannerId}</td>
        <td id="{id}_name">{name}</td>
        <td>{numPages}</td>
        <td>{archiveFile}</td>
    </tr>
    <!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}<br />
    <!--{CSV_REPORT}-->
</div>
