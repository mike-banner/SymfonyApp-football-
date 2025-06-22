// URL du fichier JavaScript ou du controller que vous souhaitez appeler
const url = 'https://127.0.0.1:8000/api/save-json-data';

// Envoyer la requête HTTP GET à l'URL du fichier JavaScript ou du controller
fetch(url, {
    method: 'GET'
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch((error) => console.error('Error:', error));