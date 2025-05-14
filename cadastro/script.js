function buscar_cep(){
    const cep = document.getElementById("cep");
    const rua = document.getElementById("rua");
    const bairro = document.getElementById("bairro");
    const city = document.getElementById("city");
    const estado = document.getElementById("estado");

    const url = "https://cep.awesomeapi.com.br/json/"+cep.value;

    fetch(url)
    .then(resposta=>resposta.json())
    .then(json=>{
        rua.value = json.address;
        bairro.value = json["district"];
        city.value = json["city"];
        estado.value = json["state"];

    });
}
