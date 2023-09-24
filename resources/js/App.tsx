import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";

const App: React.VFC = () => {
    const [words, setWords] = useState<{
        article?: string;
        webTechName?: string;
        grammarExplanation?: string;
        wordList?: string[];
    }>({});

    useEffect(() => {
        fetch("http://127.0.0.1:8000/word")
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => setWords(data))
            .catch((error) =>
                console.log(
                    "There was a problem with the fetch operation:",
                    error.message
                )
            );
    }, []);

    return (
        <div>
            <div style={{ fontSize: "50px" }}>using Word</div>
            {words.wordList &&
                words.wordList.map((word, index) => (
                    <li key={index}>
                        {index + 1}. {word}
                    </li>
                ))}

            {console.log(words)}
            <div style={{ fontSize: "50px" }}>article</div>
            {words.article}
            <div style={{ fontSize: "50px" }}>webTechName</div>
            {words.webTechName}
            <div style={{ fontSize: "50px" }}>grammarExplanation</div>
            {words.grammarExplanation}
        </div>
    );
};

export default App;

ReactDOM.render(<App />, document.getElementById("root"));
