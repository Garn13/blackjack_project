var gameDiv = document.getElementById("game");
var game = gameDiv.dataset.game;
var table = gameDiv.dataset.table;
// console.log(div.dataset.test)
var playButtons = document.querySelectorAll(".playButton");

for (const button of playButtons) {
    button.addEventListener("click", function(e){
        e.preventDefault();
        const div = button.parentElement
        let input = document.createElement("input");
        input.setAttribute("id", "betInput");
        input.setAttribute("type", "number");
        input.setAttribute("min", 1);
        input.setAttribute("max", 100);
        let submitButton = document.createElement("button");
        submitButton.setAttribute("id", "betButton");
        submitButton.textContent = "Miser";
        button.remove();
        div.appendChild(input);
        div.appendChild(submitButton);
        submitButton.addEventListener("click", function(e){
            const value = submitButton.previousSibling.value > 100 ? 100 : submitButton.previousSibling.value;
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
                    startButton.textContent = "Commencer"
                    let p = document.createElement("p");
                    p.textContent = "Mise: "+ value;
                    handDiv.appendChild(p);
                    let dealerDiv = document.getElementById("dealer-container")
                    if (dealerDiv.querySelector('#startButton') === null){
                        dealerDiv.appendChild(startButton);
                    }

                    startButton.addEventListener("click", function(e){
                        e.preventDefault();
                        startButton.remove();
                        for (const button of playButtons) {
                            button.remove();
                        }
                        
                        fetch("/game/deal?game="+ game)
                        .then(response =>  response.json())
                        .then((responseData) => {
                            console.log(responseData);
                            let hands = document.querySelectorAll("[data-hand]");
                            for (const hand of hands){
                                let div = document.createElement("div");
                                div.textContent = `${responseData[hand.dataset.hand][0][0]} ${responseData[hand.dataset.hand][0][1]}`
                                hand.prepend(div)
                            }
                            let div = document.createElement("div");
                            let dealerDiv = document.getElementById("dealer-container")
                            div.textContent = `${responseData["dealer"][0][0]} **`
                            dealerDiv.prepend(div)
                        })
                    });
                }
            })
        })
    });
}



