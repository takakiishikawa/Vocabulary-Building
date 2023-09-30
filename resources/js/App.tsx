import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";

const App: React.VFC = () => {
    const [words, setWords] = useState<{
        article?: string;
        webTechName?: string;
        grammarExplanation?: string;
        wordList?: string[];
        jpTranslation?: string;
        selectedWords?: string[];
        selectedGrammar?: string;
        selectedTechnology?: string;
        disruptionIntonation?: string;
        droppingWordPhrase?: string;
    }>({});

    //ボタンを押下すると、laravelにAPIを送るコードを実行してください
    const sendWords = () => {
        const url = "http://xxxxxx";
        const data = {
            article: words.article,
            webTechName: words.webTechName,
            grammarExplanation: words.grammarExplanation,
            wordList: words.wordList,
            jpTranslation: words.jpTranslation,
            selectedWords: words.selectedWords,
            selectedGrammar: words.selectedGrammar,
            selectedTechnology: words.selectedTechnology,
            disruptionIntonation: words.disruptionIntonation,
            droppingWordPhrase: words.droppingWordPhrase,
        };
    };

    // Fetches words from the API, and stores them in the component state.s
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
            <div style={{ fontSize: "50px" }}>selectedWords</div>
            {words.selectedWords &&
                words.selectedWords.map((selectedWord, index) => (
                    <li key={index}>
                        {index + 1}. {selectedWord}
                    </li>
                ))}

            <div style={{ fontSize: "50px" }}>article</div>
            {words.article}
            <div style={{ fontSize: "50px" }}>grammarExplanation</div>
            {words.grammarExplanation}
            <div style={{ fontSize: "50px" }}>jpTranslation</div>
            {words.jpTranslation}
            <div style={{ fontSize: "50px" }}>selectedGrammar</div>
            {words.selectedGrammar}
            <div style={{ fontSize: "50px" }}>selectedTechnology</div>
            {words.selectedTechnology}
        </div>
    );
};

export default App;

ReactDOM.render(<App />, document.getElementById("root"));
