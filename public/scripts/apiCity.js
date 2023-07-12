//Récupération des nodes et déclaration des variables
const cityNameNode = document.getElementById("city_name");
const postcodeNode = document.getElementById("city_postcode");
let data;
let lon;
let lat;
const datalist = createDataList();

//Rajout de l'attribut et de la datalist au formulaire
cityNameNode.setAttribute('list','city_list');
cityNameNode.appendChild(datalist);

//Requête API asynchrone
const getData = async ()=> {
    const response = await fetch('https://api-adresse.data.gouv.fr/search/?q='+cityNameNode.value+'&type=municipality&autocomplete=1');
    data = await response.json();
    // console.log(data.features);
    //Les résultats sont stockées dans data.features
    //Mapping des résultats trouvés. Sauvegarde des attributs id, postcode, lat et lon dans les <option> de la <datalist>
    cityName= data.features.map(result=>`<option id=${result.properties.city} postcode=${result.properties.postcode} lat=${result.geometry.coordinates[0]} lon=${result.geometry.coordinates[1]} >${result.properties.city}</option>`);
    //Ecriture dans datalist
    datalist.innerHTML=cityName.join();
}
//Event oninput pour requetage de l'api à chaque frappe
cityNameNode.oninput = getData;
//Mis à jour du code postal au changement de ville
cityNameNode.onchange = refreshPostcode;

//Récupération du code postal et des coordonnées en fonction du la ville saisie
function refreshPostcode() {
    postcodeNode.value = document.getElementById(cityNameNode.value).getAttribute('postcode');
    lat = document.getElementById(cityNameNode.value).getAttribute('lat');
    lon = document.getElementById(cityNameNode.value).getAttribute('lon');
    // console.log(postcodeNode.value)
    // console.log(lat)
    // console.log(lon)
}
//Création de la datalist
function createDataList(){
    let values = [];
    let dataList = document.createElement('datalist');
    dataList.id = "city_list";
    return dataList;
}