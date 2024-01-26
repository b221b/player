const dataForm = document.getElementById('dataForm');
const storedDataDiv = document.getElementById('storedData');

let dataStore = [];

async function saveDataToFile() {
    const fileHandle = await window.showSaveFilePicker({
        suggestedName: 'dataStore.json',
        types: [
            {
                description: 'JSON Files',
                accept: {
                    'application/json': ['.json']
                }
            }
        ]
    });

    const writableStream = await fileHandle.createWritable();
    await writableStream.write(new TextEncoder().encode(JSON.stringify(dataStore, null, 2)));
    await writableStream.close();
}

async function loadDataFromFile() {
    const fileHandle = await window.showOpenFilePicker({
        suggestedName: 'dataStore.json',
        types: [
            {
                description: 'JSON Files',
                accept: {
                    'application/json': ['.json']
                }
            }
        ]
    });

    const file = await fileHandle[0].getFile();
    const contents = await file.text();
    dataStore = JSON.parse(contents);
    renderStoredData();
}

dataForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;

    const newData = {
        id: dataStore.length + 1,
        name,
        email
    };

    dataStore.push(newData);
    saveDataToFile();

    // Clear the form fields
    document.getElementById('name').value = '';
    document.getElementById('email').value = '';
});

function renderStoredData() {
    storedDataDiv.innerHTML = '';
    dataStore.forEach((data) => {
        const dataParagraph = document.createElement('p');
        dataParagraph.textContent = `ID: ${data.id}, Name: ${data.name}, Email: ${data.email}`;
        storedDataDiv.appendChild(dataParagraph);
    });
}

// Load data from file on page load
loadDataFromFile();