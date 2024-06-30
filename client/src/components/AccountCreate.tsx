import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  TextField, Button, Container, Typography, Grid, FormControl, InputLabel, Select, MenuItem, Dialog, DialogTitle, DialogContent, DialogActions,
  SelectChangeEvent,
} from '@mui/material';
import { Account } from '../types/Account';
import InputTextField from './InputTextField';
import config from '../config';
import { Link } from 'react-router-dom';

interface Company {
  id: number;
  name: string;
  address: string | null;
}

const AccountCreate: React.FC = () => {
  const navigate = useNavigate();

  const [formData, setFormData] = useState<Account>({
    id: 0,
    first_name: '',
    last_name: '',
    email: '',
    position: '',
    phone_1: '',
    phone_2: '',
    phone_3: '',
    company_id: null,
    deleted_at: '',
  });

  const [companies, setCompanies] = useState<Company[]>([]);
  const [newCompanyName, setNewCompanyName] = useState('');
  const [newCompanyAddress, setNewCompanyAddress] = useState('');
  const [showCompanyDialog, setShowCompanyDialog] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchCompanies = async () => {
      try {
        const response = await fetch(`${config.apiUrl}/companies.php`);
        if (!response.ok) {
          throw new Error('Ошибка при загрузке списка компаний');
        }
        const data = await response.json();
        setCompanies(data.data);
      } catch (err) {
        setError('Ошибка при загрузке списка компаний');
        console.error(err);
      }
    };

    fetchCompanies();
  }, []);

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [event.target.name]: event.target.value,
    });
  };

  const handleCompanyChange = (event: SelectChangeEvent<number>) => {
    const companyId = event.target.value !== '' ? Number(event.target.value) : null;
    setFormData({ ...formData, company_id: companyId });
  };

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);
    console.log(JSON.stringify(formData));
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

  const handleCreateCompany = async () => {
    try {
      const response = await fetch(`${config.apiUrl}/companies.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newCompanyName,
          address: newCompanyAddress,
        }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка при создании компании');
      }

      const data = await response.json();
      const newCompany: Company = {
        id: data.data.id,
        name: newCompanyName,
        address: newCompanyAddress,
      };

      setCompanies([...companies, newCompany]);
      setFormData({ ...formData, company_id: newCompany.id });
      setNewCompanyName('');
      setNewCompanyAddress('');
      setShowCompanyDialog(false);
    } catch (err) {
      setError((err as Error).message);
    }
  };

  return (
    <Container maxWidth="sm">
      <Typography variant="h4" align="center" gutterBottom>
        <Button component={Link} to="/" variant="contained" color="primary" sx={{ float: 'left' }}>
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
          {/* Поля ввода (используется компонент InputTextField) */}
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
          
          {/* Select для выбора компании */}
          <Grid item xs={12}>
            <FormControl fullWidth>
              <InputLabel id="company-select-label">Компания</InputLabel>
              <Select
                labelId="company-select-label"
                id="company-select"
                name="company_id"
                value={formData.company_id || ''}
                onChange={handleCompanyChange}
                label="Компания"
              >
                <MenuItem value="">
                  <em>Выберите компанию</em>
                </MenuItem>
                {companies.map(company => (
                  <MenuItem key={company.id} value={company.id}>
                    {company.name}
                  </MenuItem>
                ))}
                <MenuItem onClick={() => setShowCompanyDialog(true)}>
                  Создать новую компанию
                </MenuItem>
              </Select>
            </FormControl>
          </Grid>
        </Grid>

        {/* Кнопка "Создать" */}
        <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
          Создать
        </Button>
      </form>

      {/* Диалог для создания новой компании */}
      <Dialog open={showCompanyDialog} onClose={() => setShowCompanyDialog(false)}>
        <DialogTitle>Создать новую компанию</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            id="name"
            label="Название компании"
            type="text"
            fullWidth
            value={newCompanyName}
            onChange={(e) => setNewCompanyName(e.target.value)}
          />
          <TextField
            margin="dense"
            id="address"
            label="Адрес"
            type="text"
            fullWidth
            value={newCompanyAddress}
            onChange={(e) => setNewCompanyAddress(e.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setShowCompanyDialog(false)}>Отмена</Button>
          <Button onClick={handleCreateCompany} color="primary">
            Создать
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
};

export default AccountCreate;