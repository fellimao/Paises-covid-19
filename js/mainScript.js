function redirecionar(url) {
    window.location.href = url;
  }


function adquireDadoPais(pais) {
   return new Promise((resolve, reject) => {
     const xhr = new XMLHttpRequest();
     xhr.open('GET', 'https://dev.kidopilabs.com.br/exercicio/covid.php?pais=' + pais);
     xhr.onload = function() {
       if (this.status === 200) {
         const response = JSON.parse(this.responseText);
         resolve(response);
       } else {
         reject(new Error('Erro ao obter dados'));
       }
     };
     xhr.onerror = function() {
       reject(new Error('Erro ao obter dados'));
     };
     xhr.send();
   });
 }