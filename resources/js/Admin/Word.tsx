import React, { useEffect, useState } from "react";

interface WordData {
    id: number;
    name: string;
    core_meaning: string;
    imagery: string;
    word_jp: string;
    parse: string;
}

const Word: React.FC = () => {
    const [wordTestList, setWordTestList] = useState<WordData[]>([]);
    console.log("wordTestList", wordTestList);

    useEffect(() => {
        fetch("http://127.0.0.1:8000/api/word/list")
            .then((res) => res.json())
            .then((data) => {
                console.log("wordTestList", data.wordTestList);
                setWordTestList(data.wordTestList);
            });
    }, []);

    const fetchWord = () => {
        fetch("http://127.0.0.1:8000/api/word/generate")
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
                }
            });
    };

    return (
        <div>
            <div>
                <button onClick={fetchWord}>Generate Word In Chat GPT</button>
            </div>
            <h2>WordTestData</h2>
            {Object.keys(wordTestList).length === 0
                ? null
                : wordTestList.map((wordData: any, index: number) => (
                      <ul key={index}>
                          <li>{wordData.id}</li>
                          <ul>
                              <li>{wordData.name}</li>
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
