import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  Button, Container, Typography, Grid 
} from '@mui/material';
import { Account } from '../types/Account';
import InputTextField from './InputTextField';
import config from '../config';
import { Link } from 'react-router-dom';

const AccountCreate: React.FC = () => {
  const navigate = useNavigate();

  const [formData, setFormData] = useState<Account>({
    id: 0,
    first_name: '',
    last_name: '',
    email: '',
    company_name: '',
    position: '',
    phone_1: '',
    phone_2: '',
    phone_3: '',
    deleted_at:'',
  });

  const [error, setError] = useState<string | null>(null);

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [event.target.name]: event.target.value,
    });
  };

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);

    try {
      const response = await fetch(`${config.apiUrl}/create.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка при создании аккаунта'); 
      }

      navigate('/'); 
    } catch (err) {
      setError((err as Error).message);
    }
  };

  return (
    <Container maxWidth="sm">
      
      <Typography variant="h4" align="center" gutterBottom>
        <Button component={Link} to="/" variant="contained" color="primary" sx={{float:'left'}}>
        ←
        </Button>
        Создать аккаунт
      </Typography>

      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      <form onSubmit={handleSubmit}>
        <Grid container spacing={2}>
          <InputTextField 
            label="Имя" 
            name="first_name" 
            value={formData.first_name} 
            onChange={handleChange} 
            required 
          />
          <InputTextField 
            label="Фамилия" 
            name="last_name" 
            value={formData.last_name} 
            onChange={handleChange} 
            required 
          />
          <InputTextField 
            label="Email" 
            name="email" 
            value={formData.email} 
            onChange={handleChange} 
            required 
          />
          <InputTextField 
            label="Название компании" 
            name="company_name" 
            value={formData.company_name} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Должность" 
            name="position" 
            value={formData.position} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Телефон 1" 
            name="phone_1" 
            value={formData.phone_1} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Телефон 2" 
            name="phone_2" 
            value={formData.phone_2} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Телефон 3" 
            name="phone_3" 
            value={formData.phone_3} 
            onChange={handleChange}  
          />
        </Grid>
        
          
         
        <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
          Создать
        </Button>
      </form>
      
    </Container>
  );
};

export default AccountCreate;