import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { 
  Button,
  Container, Typography 
} from '@mui/material';
import { Account } from '../types/Account';
import config from '../config';
import AccountForm from './AccountForm';

const AccountEdit: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const accountId = id ? parseInt(id, 10) : 0;

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
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${config.apiUrl}/index.php?id=${accountId}`, {
          method: 'GET',
        });

        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных аккаунта');
        }

        const data = await response.json();
        setFormData(data.data);
        if (typeof data.data === 'object' && data.data !== null) {
          setFormData(data.data);
        } else {
          throw new Error('Неверный формат данных аккаунта'); 
        }
      } catch (err) {
        setError('Ошибка при загрузке данных аккаунта');
        console.error(err); 
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [accountId]);

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
      const response = await fetch(`${config.apiUrl}/edit.php?id=${accountId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData), 
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка при обновлении аккаунта'); 
      }

      navigate('/'); 
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
  console.log(formData);
  return (
    <Container maxWidth="sm">
      <Typography variant="h4" align="center" gutterBottom>
      <Button component={Link} to="/" variant="contained" color="primary" sx={{float:'left'}}>
        ←
      </Button>
        Редактировать аккаунт
      </Typography>

      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      <AccountForm formData={formData} onChange={handleChange} onSubmit={handleSubmit} />
    </Container>
  );
};

export default AccountEdit;