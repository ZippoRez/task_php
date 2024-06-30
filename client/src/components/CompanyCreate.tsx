import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { 
  TextField, Button, Container, Typography, Grid 
} from '@mui/material';
import InputTextField from './InputTextField';
import config from '../config';

const CompanyCreate: React.FC = () => {
  const navigate = useNavigate();

  const [name, setName] = useState('');
  const [address, setAddress] = useState('');
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);

    const newCompany = {
      name: name, 
      address: address
    }; 

    try {
      const response = await fetch(`${config.apiUrl}/companies.php`, { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify(newCompany),
      }); 

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка при создании компании'); 
      }

      navigate('/companies'); 

    } catch (err) {
      setError((err as Error).message); 
    }
  };

  return (
    <Container maxWidth="sm">
      <Typography variant="h4" align="center" gutterBottom>
        <Button component={Link} to="/companies" variant="contained" color="primary" sx={{ float: 'left' }}>
          ←
        </Button>
        Создать компанию
      </Typography>

      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      <form onSubmit={handleSubmit}>
        <Grid container spacing={2}>
          <InputTextField 
            label="Название компании"
            name="name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required 
          />
          <InputTextField 
            label="Адрес компании"
            name="address"
            value={address}
            onChange={(e) => setAddress(e.target.value)} 
          />
        </Grid> 

        <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
          Создать
        </Button> 
      </form> 
    </Container>
  ); 
};

export default CompanyCreate;