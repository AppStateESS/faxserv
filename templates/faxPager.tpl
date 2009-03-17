<table cellpadding="3">
    <tr>
        <th>File Name</th>
        <th>Sender Phone</th>
        <th>Date Received</th>
        <th>Printed</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="3">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE} {new}>
        <td>{fileName}</td>
        <td>{senderPhone}</td>
        <td>{dateReceived}</td>
        <td>{printed}</td>
        <td>{actions}</td>
    </tr>
    <!-- END listrows -->
</table>

