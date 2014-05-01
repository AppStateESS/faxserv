<div align="right">
    {SEARCH}
</div>

<table class="table table-striped">
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
    <tr>
	    <td>{FAXNAME}</td>
        <td>{USERNAME}</td>
        <td>{ACTIVITY}</td>
        <td>{TIMEPERFORMED}</td>
    </tr>
    <!-- END listrows -->
</table>

<div class="row">
  <div class="col-md-4 col-md-offset-4">
    <p class="text-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}<br />
    {CSV_REPORT}
    </p>
  </div>
</div>
