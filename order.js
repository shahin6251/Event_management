// Example order data (replace with backend/API later)
const orders = [
  { id: 1, event: "Wedding", customer: "Alice", date: "2025-10-20", status: "Pending" },
  { id: 2, event: "Birthday Party", customer: "Bob", date: "2025-10-22", status: "Confirmed" },
  { id: 3, event: "Corporate Event", customer: "Charlie", date: "2025-10-25", status: "Completed" }
];

const tableBody = document.getElementById("orderTableBody");

orders.forEach(order => {
  const row = document.createElement("tr");

  // Create cells
  row.innerHTML = `
    <td>${order.id}</td>
    <td>${order.event}</td>
    <td>${order.customer}</td>
    <td>${order.date}</td>
    <td class="statusCell">${order.status}</td>
    <td>
      <button class="actionBtn approve">Approve</button>
      <button class="actionBtn reject">Reject</button>
      <button class="actionBtn complete">Complete</button>
    </td>
  `;

  // Add event listeners for buttons
  const statusCell = row.querySelector(".statusCell");
  row.querySelector(".approve").addEventListener("click", () => {
    statusCell.textContent = "Approved";
    statusCell.style.color = "#28a745";
  });
  row.querySelector(".reject").addEventListener("click", () => {
    statusCell.textContent = "Rejected";
    statusCell.style.color = "#dc3545";
  });
  row.querySelector(".complete").addEventListener("click", () => {
    statusCell.textContent = "Completed";
    statusCell.style.color = "#007bff";
  });

  tableBody.appendChild(row);
});