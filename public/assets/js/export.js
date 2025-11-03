function exportTable(selectedHeaders) {
    // Get the table element
    var table = document.getElementById("myTable");

    // Filter table headers based on selected headers
    var headers = Array.from(table.querySelectorAll("th")).filter((th) =>
        selectedHeaders.includes(th.textContent.trim())
    );

    // Create a new table element with only selected headers and their corresponding data
    var newTable = document.createElement("table");
    var thead = document.createElement("thead");
    var tbody = document.createElement("tbody");

    // Append selected headers to the new table
    var headerRow = document.createElement("tr");
    headers.forEach((header) => {
        var th = document.createElement("th");
        th.textContent = header.textContent.trim();
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    newTable.appendChild(thead);

    // Copy corresponding data to the new table
    var rows = Array.from(table.querySelectorAll("tbody > tr"));
    rows.forEach((row) => {
        var newRow = document.createElement("tr");
        headers.forEach((header) => {
            var cell = row.querySelector(
                "td:nth-child(" +
                    (Array.from(row.parentNode.children).indexOf(row) + 1) +
                    ")"
            );
            var td = document.createElement("td");
            td.textContent = cell.textContent.trim();
            newRow.appendChild(td);
        });
        tbody.appendChild(newRow);
    });
    newTable.appendChild(tbody);

    // Create a worksheet from the new table
    var ws = XLSX.utils.table_to_sheet(newTable);

    // Create a workbook
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    // Save the workbook as an Excel file
    var fileName = "table_export.xlsx";
    XLSX.writeFile(wb, fileName);
}

// Example usage:
var selectedHeaders = ["Name", "No of Assigned User", "Status"]; // Put the headers you want to export here
exportTable(selectedHeaders);
