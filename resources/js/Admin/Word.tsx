import React, { useState } from "react";

const Word: React.FC = () => {
    const [words, setWords] = useState<Record<string, any>>({});

    const fetchWord = () => {
        fetch("http://127.0.0.1:8000/api/word")
            .then((res) => res.json())
            .then((data) => {
                setWords(data.wordGptData);
            });
    };

    const saveWord = () => {
        fetch("http://127.0.0.1:8000/api/word/save", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(words),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.message) {
                    alert(data.message);
                } else if (data.error) {
                    alert(data.error);
                }
            });
    };

    return (
        <div>
            <div>
                <button onClick={fetchWord}>Generate word In Chat GPT</button>
            </div>
            {console.log(words)}
            {Object.keys(words).length === 0
                ? null
                : Object.entries(words).map(([wordKey, wordData], index) => (
                      <ul key={index}>
                          <li>
                              {index + 1}.{wordKey}
                          </li>
                          <li>core_meaning: {wordData.core_meaning}</li>
                          <li>imagery: {wordData.imagery}</li>
                          <li>word_jp: {wordData.word_jp}</li>
                          <li>parse: {wordData.parse}</li>
                      </ul>
                  ))}
            <div>
                <button onClick={saveWord}>Save To DB</button>
            </div>
        </div>
    );
};

export default Word;
