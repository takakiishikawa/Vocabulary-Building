import { configureStore } from "@reduxjs/toolkit";
import { combineReducers } from "redux";
import counterReducer from "./counter/counterSlice";

const store = configureStore({
    reducer: {
        counter: counterReducer,
    },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export default store;

//storeは、getStateとdispatchメソッドを保有する
//storeはシングルトンである
//reducerは、現在のstate + dispatchされたaction 引数とする、純粋関数

//1.dispatchされた後の処理:action(type Objを保有しており、actionの識別が可能)
//2.→ store　→　rootReducer(全sliceのreducerの集合体) → slice → reducer →　新しいstate → Re Rednering → UI change
