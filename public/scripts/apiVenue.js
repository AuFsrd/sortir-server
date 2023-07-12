const cityNameNode = document.getElementById("venue_city");
const streetNode = document.getElementById("venue_street");
const latitudeNode = document.getElementById("venue_latitude");
const longitudeNode = document.getElementById("venue_longitude");
let data;
let lon;
let lat;
const datalist = createDataList();
const getData = async ()=> {
    let formattedStreetString = streetNode.value.replaceAll(" ", "%20");
    const response = await fetch('https://api-adresse.data.gouv.fr/search/?q='+formattedStreetString+"%20"+getSelectValue()+'&type=&autocomplete=1');
    data = await response.json();
    console.log(data.features);
    streets= data.features.map(result=>`<option id="${result.properties.name}" lat="${result.geometry.coordinates[0]}" lon="${result.geometry.coordinates[1]}" >${result.properties.name}</option>`);
    console.log(streets)
    datalist.innerHTML=streets.join();
}
streetNode.oninput = getData;
streetNode.onchange = refreshPostcode;
streetNode.setAttribute('list','street_list');
streetNode.appendChild(datalist);

function refreshPostcode() {
    /*postcodeNode.value = document.getElementById(cityNameNode.value).getAttribute('postcode');*/
    latitudeNode.value = document.getElementById(streetNode.value).getAttribute('lat');
    longitudeNode.value = document.getElementById(streetNode.value).getAttribute('lon');
    console.log(lat)
    console.log(lon)
}
function createDataList(){
    let values = [];
    let dataList = document.createElement('datalist');
    dataList.id = "street_list";
    return dataList;
}

function getSelectValue()
{
    return cityNameNode.options[cityNameNode.selectedIndex].innerText;
}