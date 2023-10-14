import { createSlice } from "@reduxjs/toolkit";
import type { PayloadAction } from "@reduxjs/toolkit";

export interface CounterState {
    value: number;
}

const initialState: CounterState = {
    value: 0,
};

export const incrementAsync = (amount: number) => (dispatch) => {
    setTimeout(() => {
        dispatch(incrementByAmount(amount));
    }, 1000);
};

export const counterSlice = createSlice({
    name: "counter",
    initialState,
    reducers: {
        increment: (state) => {
            state.value++;
        },
        decrement: (state) => {
            state.value--;
        },
        incrementByAmount: (state, action: PayloadAction<number>) => {
            state.value += action.payload;
        },
    },
});

//createSliceにより、action creater　と　reducer　が作成される
//counterSlice.reducerの役割は、storeに渡すreducerを作成すること　など
export const { increment, decrement, incrementByAmount } = counterSlice.actions;
export default counterSlice.reducer;

//下記のように、action typeも自動生成されている
//counterSlice.reducers.increment.type
//action typeは、actionの識別子のようなもの
