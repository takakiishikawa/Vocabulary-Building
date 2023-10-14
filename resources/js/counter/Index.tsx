import React from "react";
import { useAppDispatch, useAppSelector } from "@/hooks";
import { decrement, increment } from "./counterSlice";

export function Index() {
    const count = useAppSelector((state) => state.counter.value);
    const dispatch = useAppDispatch();

    return (
        <div>
            <button onClick={() => dispatch(increment())}>increment</button>
            <span>{count}</span>
            <button onClick={() => dispatch(decrement())}>decrement</button>
        </div>
    );
}

export default Index;
