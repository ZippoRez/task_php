import React from 'react';
import { TextField, Grid } from '@mui/material'; //  Компоненты Material-UI

//  Интерфейс для свойств компонента InputTextField
interface InputTextFieldProps {
  label: string; //  Метка (label) текстового поля
  name: string; //  Имя поля (используется для идентификации поля и при отправке формы)
  value: string | null; //  Значение поля 
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void; //  Обработчик изменения значения поля
  required?: boolean; //  Флаг,  указывающий,  является ли поле обязательным (необязательное свойство)
}

//  Компонент InputTextField - обертка над TextField Material-UI
const InputTextField: React.FC<InputTextFieldProps> = ({ label, name, value, onChange, required }) => {
  return (
    //  Используем Grid для размещения TextField
    <Grid item xs={12}> 
      <TextField
        label={label} 
        name={name} 
        fullWidth //  Занимает всю доступную ширину
        value={value} 
        onChange={onChange} 
        required={required} //  Устанавливаем required,  если свойство передано
      />
    </Grid>
  );
};

export default InputTextField;