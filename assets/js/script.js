function validateCustomer() {
  let name = document.getElementById("name").value;
  if (name == "") {
    alert("Name is required");
    return false;
  }
  return true;
}

function validateTransaction() {
  let cid = document.getElementById("customer_id").value;
  if (cid == "") {
    alert("Customer ID required");
    return false;
  }
  return true;
}
