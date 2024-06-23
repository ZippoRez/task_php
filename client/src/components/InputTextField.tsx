import React from 'react';
import { TextField, Grid } from '@mui/material';

interface InputTextFieldProps {
  label: string;
  name: string;
  value: string | null;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  required?: boolean; 
}

const InputTextField: React.FC<InputTextFieldProps> = ({ label, name, value, onChange, required }) => {
// const stringValue = value === null ? '' : value;
  return (
    <Grid item xs={12}>
      <TextField
        label={label}
        name={name}
        fullWidth
        value={value}
        onChange={onChange}
        required={required} 
      />
    </Grid>
  );
};

export default InputTextField;