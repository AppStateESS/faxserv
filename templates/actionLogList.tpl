<div align="right">
    {SEARCH}
</div>

<table class="faxmaster_actionLogList" cellpadding="2">
    <tr>
	<th>Fax Name</th>
        <th>Username</th>
        <th>Action</th>
        <th>Timestamp</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="3">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
	<td>{FAXNAME}</td>
        <td>{USERNAME}</td>
        <td>{ACTIVITY}</td>
        <td>{TIMEPERFORMED}</td>
    </tr>
    <!-- END listrows -->
</table>
