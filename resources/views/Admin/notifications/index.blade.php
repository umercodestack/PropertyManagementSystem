@extends('Admin.layouts.app')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Role-Based Notifications</h3>
        </div>
    </div>
</div>

<div class="card card-bordered mt-3">
    <div class="card-inner">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                Show 
                <select id="rowsPerPage" class="form-select d-inline-block w-auto">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select> entries
            </div>
            <div class="ms-auto">
                <input type="text" id="searchInput" class="form-control" placeholder="Search Notifications..." />
            </div>
        </div>

        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th class="sortable">#</th>
                    <th class="sortable">Title</th>
                    <th class="sortable">Message</th>
                    <th class="sortable">Status</th>
                    <th class="sortable">Date</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Data will be populated dynamically -->
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center" style="margin-top:25px">
            <div id="tableInfo" class="text-muted"></div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>
<script>
  let currentPage = 1;
let rowsPerPage = 10;
let searchQuery = '';

// Render the table and make the request to the server using jQuery AJAX
function fetchData() {
    $.ajax({
        url: `/notifications/show/`,
        method: 'GET',
        data: {
            page: currentPage,
            rowsPerPage: rowsPerPage,
            search: searchQuery
        },
        success: function (data) {
            renderTable(data.notifications);
            renderPagination(data.totalPages);
            updateTableInfo(data.totalEntries);
        },
        error: function (error) {
            console.error('Error fetching data:', error);
        }
    });
}


function renderTable(notifications) {
    const tableBody = $('#tableBody');
    tableBody.empty(); 
    notifications.forEach((notification, index) => {
        const dateFormatted = new Date(notification.created_at).toLocaleString();  // Format the date

        const row = `
            <tr>
                <td>${(currentPage - 1) * rowsPerPage + index + 1}</td>
                <td>${notification.title}</td>
                <td><a href="${notification.url}" target="_blank">${notification.message}</a></td>
                <td>
                    <span class="badge bg-${notification.is_seen_by_all === 1 ? 'success' : 'warning'}">
                        ${notification.is_seen_by_all === 1 ? 'Seen' : 'Unseen'}
                    </span>
                </td>
                <td>${dateFormatted}</td>
            </tr>
        `;
        tableBody.append(row);
    });
}

// Render pagination
function renderPagination(totalPages) {
    const pagination = $('#pagination');
    pagination.empty(); // Clear previous content

    // First and Previous Buttons
    const firstPage = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#">First</a>
                    </li>`;
    const prevPage = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#">Previous</a>
                    </li>`;

    // Append First and Previous buttons
    pagination.append(firstPage);
    pagination.append(prevPage);

    // Page Numbers
    const pageLinks = [];
    for (let i = 1; i <= totalPages; i++) {
        pageLinks.push(`
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#">${i}</a>
            </li>
        `);
    }
    pagination.append(pageLinks.join(''));

    // Next and Last Buttons
    const nextPage = `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#">Next</a>
                    </li>`;
    const lastPage = `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#">Last</a>
                    </li>`;

    // Append Next and Last buttons
    pagination.append(nextPage);
    pagination.append(lastPage);

    // Add Event Listeners for Page Navigation
    pagination.find('.page-link').on('click', function (e) {
        e.preventDefault();
        const clickedPage = $(this).text();

        if (clickedPage === "First") {
            currentPage = 1;
        } else if (clickedPage === "Previous") {
            if (currentPage > 1) currentPage--;
        } else if (clickedPage === "Next") {
            if (currentPage < totalPages) currentPage++;
        } else if (clickedPage === "Last") {
            currentPage = totalPages;
        } else {
            currentPage = parseInt(clickedPage);
        }

        fetchData(); // Re-fetch data with the updated page number
    });
}


// Update table info (total entries)
function updateTableInfo(totalEntries) {
    const tableInfo = $('#tableInfo');
    const start = (currentPage - 1) * rowsPerPage + 1;
    const end = Math.min(currentPage * rowsPerPage, totalEntries);
    tableInfo.text(`Showing ${start} to ${end} of ${totalEntries} entries`);
}

// Handle search input
$('#searchInput').on('input', function (e) {
    searchQuery = e.target.value;
    currentPage = 1; // Reset to page 1 for new search
    fetchData();
});

// Handle rows per page change
$('#rowsPerPage').on('change', function (e) {
    rowsPerPage = e.target.value;
    currentPage = 1; // Reset to page 1 for new rows per page
    fetchData();
});

// Initial fetch on page load
$(document).ready(function () {
    fetchData();
});
</script>    
@endsection



