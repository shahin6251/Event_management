// Handle Edit Buttons
document.querySelectorAll('.editBtn').forEach(btn => {
  btn.addEventListener('click', () => {
    const targetId = btn.getAttribute('data-target');
    const targetEl = document.getElementById(targetId);

    // If already editing, do nothing
    if (targetEl.querySelector('textarea')) return;

    const currentContent = targetEl.innerHTML;
    const input = document.createElement('textarea');
    input.value = targetEl.innerText;
    input.style.width = "100%";
    input.style.height = "100px";

    // Save button
    const saveBtn = document.createElement('button');
    saveBtn.innerText = "Save";
    saveBtn.style.marginTop = "5px";
    saveBtn.style.display = "block";

    targetEl.innerHTML = "";
    targetEl.appendChild(input);
    targetEl.appendChild(saveBtn);

    saveBtn.addEventListener('click', () => {
      targetEl.innerHTML = input.value.replace(/\n/g, "<br>");
    });
  });
});