const editButton = document.querySelector('.edit-button');
const emailInfo = document.querySelector('.info:nth-child(2)');
const emailInput = document.createElement('input');

editButton.addEventListener('click', () => {
    if (editButton.textContent === 'Редактировать') {
        editButton.textContent = 'Сохранить';
        emailInfo.style.display = 'none';
        emailInput.type = 'email';
        emailInput.value = emailInfo.textContent.trim();
        emailInfo.parentElement.insertBefore(emailInput, emailInfo.nextSibling);
    } else {
        editButton.textContent = 'Редактировать';
        emailInfo.style.display = 'inline';
        emailInfo.textContent = emailInput.value;
        emailInput.remove();
    }
});