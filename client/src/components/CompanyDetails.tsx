import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { Typography, List, ListItem, ListItemText } from '@mui/material';

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

const CompanyDetails: React.FC = () => {
    const { id } = useParams<{ id: string }>(); 
    const companyId = id ? parseInt(id,  10) : 0; 

    const [company,  setCompany] = useState<Company | null>(null); 
    const [employees,  setEmployees] = useState<Employee[]>([]); 
    const [loading, setLoading] = useState(true);
    const [error,  setError] = useState<string | null>(null); 

    useEffect(() => {
        const fetchCompanyData = async () => {
            try {
                const companyResponse = await fetch(`http://your-backend/company.php?id=${companyId}`); 
                if (!companyResponse.ok) {
                    throw new Error('Ошибка при загрузке данных компании'); 
                }
                const companyData = await companyResponse.json();
                setCompany(companyData.data); 

                const employeesResponse = await fetch(`http://your-backend/company_employees.php?id=${companyId}`);
                if (!employeesResponse.ok) {
                    throw new Error('Ошибка при загрузке сотрудников'); 
                }
                const employeesData = await employeesResponse.json();
                setEmployees(employeesData.data); 

            } catch (err) {
                setError('Ошибка при загрузке данных');
                console.error(err); 
            } finally {
                setLoading(false); 
            }
        };

        fetchCompanyData(); 
    }, [companyId]); 

    if (loading) {
        return <Typography variant="body1">Загрузка данных...</Typography>;
    }

    if (error) {
        return <Typography color="error" variant="body1">Ошибка: {error}</Typography>; 
    }

    if (!company) {
        return <Typography variant="body1">Компания не найдена</Typography>; 
    }

    return (
        <div>
            <Typography variant="h4" gutterBottom>
                {company.name}
            </Typography>
            <Typography variant="body1" gutterBottom>
                Адрес: {company.address}
            </Typography>

            <Typography variant="h6" gutterBottom>
                Сотрудники:
            </Typography>

            <List>
                {employees.map(employee => (
                    <ListItem key={employee.id}>
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