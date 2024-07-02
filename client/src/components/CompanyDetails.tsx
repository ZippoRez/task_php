import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom'; 
import { Typography, List, ListItem, ListItemText, Button } from '@mui/material';
import config from '../config';

// Интерфейсы для данных компании и сотрудника
interface Company {
  id: number;
  name: string;
  address: string;
}

interface Employee {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
}

// Компонент для отображения детальной информации о компании
const CompanyDetails: React.FC = () => {
  // Получаем ID компании из URL
  const { id } = useParams<{ id: string }>();
  const companyId = id ? parseInt(id, 10) : 0;

  // Состояния для данных компании, сотрудников, загрузки и ошибки
  const [company, setCompany] = useState<Company | null>(null);
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // useEffect для загрузки данных компании и ее сотрудников при изменении companyId
  useEffect(() => {
    const fetchCompanyData = async () => {
      try {
        // Загрузка данных компании
        const companyResponse = await fetch(`${config.apiUrl}/company.php?id=${companyId}`); 
        if (!companyResponse.ok) {
          throw new Error('Ошибка при загрузке данных компании'); 
        }
        const companyData = await companyResponse.json(); 
        setCompany(companyData.data); 

        // Загрузка списка сотрудников компании
        const employeesResponse = await fetch(`${config.apiUrl}/company_employees.php?id=${companyId}`);
        if (!employeesResponse.ok) {
          throw new Error('Ошибка при загрузке сотрудников'); 
        }
        const employeesData = await employeesResponse.json(); 
        setEmployees(employeesData.data); 

      } catch (err) {
        // Обработка ошибки при загрузке данных
        setError('Ошибка при загрузке данных');
        console.error(err); 
      } finally {
        // Устанавливаем loading в false после завершения загрузки (успешной или неуспешной)
        setLoading(false); 
      }
    }; 

    // Вызываем функцию загрузки данных
    fetchCompanyData();
  }, [companyId]); 

  // Условный рендеринг в зависимости от состояния загрузки, ошибки и наличия данных компании
  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>; 
  }

  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>; 
  }

  if (!company) {
    return <Typography variant="body1">Компания не найдена</Typography>; 
  }

  // Отображение детальной информации о компании и ее сотрудниках
  return (
    <div>
        {/* Название компании */}
        <Typography variant="h4" gutterBottom>
            {company.name}
        </Typography>
        {/* Адрес компании */}
        <Typography variant="body1" gutterBottom>
            Адрес: {company.address}
        </Typography>
        {/* Кнопка "Редактировать компанию" */}
        <Button component={Link} to={`/companies/edit/${company.id}`} variant="contained" color="primary">
            Редактировать компанию
        </Button>
        {/* Заголовок списка сотрудников */}
        <Typography variant="h6" gutterBottom>
            Сотрудники:
        </Typography>

        {/* Список сотрудников */}
        <List>
            {employees.map((employee) => (
                <ListItem key={employee.id}>
                    {/* Имя и email сотрудника */}
                    <ListItemText
                        primary={`${employee.first_name} ${employee.last_name}`}
                        secondary={employee.email}
                    />
                </ListItem>
            ))}
        </List>
    </div>
  ); 
};

export default CompanyDetails;