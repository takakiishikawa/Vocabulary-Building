import React, { useState } from "react";

interface WordData {
    id: number;
    word: string;
    core_meaning: string;
    imagery: string;
    word_jp: string;
    parse: string;
}

const Word: React.FC = () => {
    const [wordTestList, setWordTestList] = useState<WordData[]>([]);

    const fetchWord = () => {
        fetch("http://127.0.0.1:8000/api/word")
            .then((res) => res.json())
            .then((data) => {
                setWordTestList(data.wordTestList);
                alert(data.message);
            });
    };

    const saveWord = () => {
        fetch("http://127.0.0.1:8000/api/word/save", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(wordTestList),
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
                <button onClick={fetchWord}>Generate Word In Chat GPT</button>
            </div>
            {Object.keys(wordTestList).length === 0
                ? null
                : wordTestList.map((wordData: any, index: number) => (
                      <ul key={index}>
                          <li>{wordData.id}</li>
                          <ul>
                              <li>{wordData.word}</li>
                              <li>{wordData.core_meaning}</li>
                              <li>{wordData.imagery}</li>
                              <li>{wordData.word_jp}</li>
                              <li>{wordData.parse}</li>
                          </ul>
                      </ul>
                  ))}
            <div>
                <button onClick={saveWord}>Save To Production DB</button>
            </div>
        </div>
    );
};

export default Word;
