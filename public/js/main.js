

let gameDiv = document.getElementById("game");
let game = gameDiv.dataset.game;
let table = gameDiv.dataset.table;
let minBet = gameDiv.dataset.minbet;
let maxBet = gameDiv.dataset.maxbet;
let playButton = document.getElementById("playButton");


playButton.addEventListener("click", function(e){
    e.preventDefault();
    const div = playButton.parentElement
    let input = document.createElement("input");
    input.setAttribute("id", "betInput");
    input.setAttribute("type", "number");
    input.setAttribute("min", minBet);
    input.setAttribute("max", maxBet);
    let submitButton = document.createElement("button");
    submitButton.setAttribute("id", "betButton");
    submitButton.textContent = "Miser";
    playButton.remove();
    div.appendChild(input);
    div.appendChild(submitButton);
    submitButton.addEventListener("click", function(e){
        const value = submitButton.previousSibling.value > parseInt(maxBet) ? parseInt(maxBet) : submitButton.previousSibling.value;
        fetch("/bet/create/?bet="+ value +"&game="+ game)
        .then(response =>  response.json())
        .then((responseData) => {
            if (responseData !== "error"){
                handDiv = submitButton.parentElement;
                handDiv.dataset.bet = responseData.bet;
                handDiv.dataset.hand = responseData.hand;
                submitButton.remove();
                input.remove();
                let startButton = document.createElement("button");
                startButton.setAttribute("id", "startButton");
                startButton.textContent = "Commencer";
                let p = document.createElement("p");
                p.textContent = "Mise: "+ responseData.betAmount;
                handDiv.appendChild(p);
                let dealerDiv = document.getElementById("dealer-container")
                if (dealerDiv.querySelector('#startButton') === null){
                    dealerDiv.appendChild(startButton);
                }

                startButton.addEventListener("click", function(e){
                    e.preventDefault();
                    startButton.remove();
                    playButton.remove();
                    
                    fetch("/game/deal?game="+ game)
                    .then(response =>  response.json())
                    .then((responseData) => {
                        console.log(responseData);
                        hand = document.getElementById('hand');
                        let div = document.createElement("div");
                        div.setAttribute("id", "cards-container");
                        const card1 = document.createElement("img");
                        card1.src = "../images/"+ responseData[hand.dataset.hand][0][0] +".png"
                        card1.setAttribute("class", "card");
                        const card2 = document.createElement("img");
                        card2.src = "../images/"+ responseData[hand.dataset.hand][0][1] +".png"
                        card2.setAttribute("class", "card");
                        console.log(responseData[hand.dataset.hand][0][0])
                        div.appendChild(card1);
                        div.appendChild(card2);
                        hand.prepend(div);
                        let divValue = document.createElement("div");
                        divValue.setAttribute("id", "value");
                        divValue.textContent = "Valeur: "+ responseData[hand.dataset.hand][2];
                        hand.appendChild(divValue);

                        let dealerCardsDiv = document.createElement("div");
                        let dealerDiv = document.getElementById("dealer-container");
                        const dealerCard1 = document.createElement("img");
                        dealerCard1.src = "../images/"+ responseData["dealer"][0] +".png"
                        dealerCard1.setAttribute("class", "card");
                        const dealerCard2 = document.createElement("img");
                        dealerCard2.src = "../images/back.png"
                        dealerCard2.setAttribute("class", "card");
                        dealerCardsDiv.appendChild(dealerCard1);
                        dealerCardsDiv.appendChild(dealerCard2);
                        dealerDiv.prepend(dealerCardsDiv);

                        let divDealer = document.createElement("div");
                        divDealer.setAttribute("id", "value-dealer");
                        divDealer.textContent = "Valeur: "+ responseData["dealer"][2];
                        dealerDiv.appendChild(divDealer);
                        let status = responseData[hand.dataset.hand][1];
                        if (status == "playing"){
                            let hitButton = document.createElement("button");
                            hitButton.setAttribute("id", "hitButton");
                            hitButton.textContent = "Hit";
    
                            let stayButton = document.createElement("button");
                            stayButton.setAttribute("id", "stayButton");
                            stayButton.textContent = "Stay";
                            
                            hand.appendChild(hitButton);
                            hand.appendChild(stayButton);
                        }else if(status == "blackjack"){
                            let h1 = document.createElement("h1");
                            DealerTurn();
                        }else if(status === "1" || status === "2"){
                            if (status === "1"){
                                let choice1 = document.createElement("button");
                                choice1.setAttribute("id", "FirstChoiceButton");
                                choice1.textContent = status === "1" ?  responseData[hand.dataset.hand][2] + 1 : 2
    
                                let choice2 = document.createElement("button");
                                choice2.setAttribute("id", "SecondChoiceButton");
                                choice2.textContent = status === "1" ?  responseData[hand.dataset.hand][2] + 11 : 12
                                
                                choiceListeners(choice1, choice2)
                                

                            }
                        }

                    })
                });
            }
        })
    })
});




const buttonsListeners = function(handId){
    let hitButton = document.getElementById("hitButton");
    let stayButton = document.getElementById("stayButton");
    console.log(hitButton)
    hitButton.addEventListener("click", function(e){
        e.preventDefault();
        fetch("/hand/"+ handId +"/hit")
        .then(response =>  response.json())
        .then((response) => {
            console.log(response)
           const cardContainer = document.getElementById("cards-container");
           const newCard = document.createElement("img");
           newCard.src = "../images/"+ response["card"] +".png"
           newCard.setAttribute("class", "card");
           cardContainer.appendChild(newCard);
           const valueDiv = document.getElementById("value");
           valueDiv.textContent = "Valeur: "+ response["value"];
           if (response["status"] === "choosing"){
               hitButton.remove();
               stayButton.remove();
               let choice1 = document.createElement("button");
                choice1.setAttribute("id", "FirstChoiceButton");
                choice1.textContent = response["value"] + 1
    
                let choice2 = document.createElement("button");
                choice2.setAttribute("id", "SecondChoiceButton");
                choice2.textContent = response["value"] + 11
                
                choiceListeners(choice1, choice2)
           }else if(response["status"] === "won"){
                hitButton.remove();
                stayButton.remove();
                DealerTurn();
           }else if(response["status"] === "bust"){
                hitButton.remove();
                stayButton.remove();
                DealerTurn();
                
           }

        });
    });
    stayButton.addEventListener("click", function(e){
        e.preventDefault();
        DealerTurn();
        
    });
}

const choiceListeners = function(choice1, choice2){
    hand = document.getElementById('hand');
    hand.appendChild(choice1);
    hand.appendChild(choice2);
    choice1.addEventListener("click", function(e){
        e.preventDefault();
        let handId = choice1.parentElement.dataset.hand
        choice1.remove();
        choice2.remove();
        fetch("/hand/"+ handId +"/choose?choice="+ 1)
        .then(response =>  response.json())
        .then((response) => {
            const divValue = document.getElementById("value");
            divValue.textContent = "Valeur: "+ response.value;
            let hitButton = document.createElement("button");
            hitButton.setAttribute("id", "hitButton");
            hitButton.textContent = "Hit";

            let stayButton = document.createElement("button");
            stayButton.setAttribute("id", "stayButton");
            stayButton.textContent = "Stay";
            
            hand.appendChild(hitButton);
            hand.appendChild(stayButton);
            buttonsListeners(handId);
        })
    });
    choice2.addEventListener("click", function(e){
        e.preventDefault();
        let handId = choice2.parentElement.dataset.hand
        choice1.remove();
        choice2.remove();
        fetch("/hand/"+ handId +"/choose?choice="+ 2)
        .then(response =>  response.json())
        .then((response) => {
            const divValue = document.getElementById("value");
            divValue.textContent = "Valeur: "+ response.value;
            let hitButton = document.createElement("button");
            hitButton.setAttribute("id", "hitButton");
            hitButton.textContent = "Hit";

            let stayButton = document.createElement("button");
            stayButton.setAttribute("id", "stayButton");
            stayButton.textContent = "Stay";
            
            hand.appendChild(hitButton);
            hand.appendChild(stayButton);
            buttonsListeners(handId);
        })
    });
}

const DealerTurn = function(){
    fetch("/game/"+ game +"/dealerturn")
    .then(response =>  response.json())
    .then((response) => {
        console.log(response)
        const dealerContainer = document.getElementById("dealer-container");
        const div = document.createElement("div");
        response.cards.forEach(card => {
            const newCard = document.createElement("img");
           newCard.src = "../images/"+ card +".png"
           newCard.setAttribute("class", "card");
            div.appendChild(newCard);
        })
        dealerContainer.replaceChildren(div);
        let message = "";
        if(response.result == "draw"){
            message = "DRAW!"
        }else if(response.result == "lose"){
            message = "YOU LOSE!"
        }else if(response.result == "win"){
            message = "YOU WIN!"
        }
        let h1 = document.createElement("h1");
            h1.textContent = message
            let resultDiv = document.getElementById("result-container");
            resultDiv.appendChild(h1);
    });

}