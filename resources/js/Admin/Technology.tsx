import React, { useState } from "react";

const Technology: React.FC = () => {
    const [technology, setTechnology] = useState<Record<string, any>>([]);

    const fetchTechnology = () => {
        fetch("http://127.0.0.1:8000/api/technology")
            .then((res) => res.json())
            .then((data) => {
                console.log(data);
            });
    };
    return <div></div>;
};

export default Technology;
