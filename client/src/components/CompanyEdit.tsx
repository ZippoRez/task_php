import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { 
    TextField, Button, Container, Typography, Grid 
} from '@mui/material';
import InputTextField from './InputTextField';
import config from '../config';

const CompanyEdit: React.FC = () => {
  const { id } = useParams<{ id: string }>(); 
  const navigate = useNavigate(); 
  const companyId = id ? parseInt(id,  10) : 0; 

  const [name,  setName] = useState(''); 
  const [address,  setAddress] = useState(''); 
  const [error,  setError] = useState<string | null>(null); 
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${config.apiUrl}/company.php?id=${companyId}`); 
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных компании'); 
        }
        const data = await response.json(); 
        setName(data.data.name);
        setAddress(data.data.address); 
      } catch (err) {
        setError('Ошибка при загрузке данных компании'); 
        console.error(err); 
      } finally {
        setLoading(false); 
      }
    }; 

    fetchData(); 
  },  [companyId]); 

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault(); 
    setError(null); 

    const updatedCompany = {
      id: companyId, 
      name: name, 
      address: address
    }; 

    try {
      const response = await fetch(`${config.apiUrl}/company.php?id=${companyId}`,  {
        method: 'PUT', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify(updatedCompany) 
      });

      if (!response.ok) {
        const errorData = await response.json(); 
        throw new Error(errorData.error ||  'Ошибка при обновлении компании'); 
      }

      navigate('/companies'); 

    } catch (err) {
      setError((err as Error).message); 
    }
  };

  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>;
  }

  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>;
  }

  return (
    <Container maxWidth="sm">
      <Typography variant="h4" align="center" gutterBottom>
        <Button component={Link} to="/companies" variant="contained" color="primary" sx={{ float: 'left' }}>
          ←
        </Button>
        Редактировать компанию
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
          Сохранить
        </Button> 
      </form> 
    </Container>
  ); 
};

export default CompanyEdit;