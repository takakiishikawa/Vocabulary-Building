import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";
import { selectTechnology } from "../TechnologySlice";

interface TechnologyData {
    id: number;
    name: string;
}

const Technology: React.FC = () => {
    const [technologyList, setTechnologyList] = useState<TechnologyData[]>([]);
    const techonology = useSelector(selectTechnology);

    useEffect(() => {
        fetch("http://127.0.0.1:8000/api/technology")
            .then((res) => res.json())
            .then((data) => {
                setTechnologyList(data.technologyList);
            });
    }, []);

    return (
        <div>
            <ul>
                {technologyList.map((TechnologyData: any, key: number) => (
                    <li key={key}>
                        {TechnologyData.id}.{TechnologyData.name}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default Technology;
