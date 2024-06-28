import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
  Paper, Button, Typography, Pagination, Select, MenuItem, FormControl, InputLabel,
  SelectChangeEvent
} from '@mui/material';
import config from '../config';

interface Company {
  id: number;
  name: string;
  address: string;
}

const CompanyList: React.FC = () => {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Состояния для пагинации
  const [currentPage, setCurrentPage] = useState(1);
  const [companiesPerPage, setCompaniesPerPage] = useState(10);
  const [totalCompanies, setTotalCompanies] = useState(0);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${config.apiUrl}/companies.php?page=${currentPage}&limit=${companiesPerPage}`);
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных');
        }
        const data = await response.json();
        setCompanies(data.data);
        setTotalCompanies(data.pagination.totalItems);
      } catch (err) {
        setError('Ошибка при загрузке данных');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [currentPage, companiesPerPage]);

  const handleChangePage = (event: React.ChangeEvent<unknown>, newPage: number) => {
    setCurrentPage(newPage);
  };

  const handleChangeRowsPerPage = (event: SelectChangeEvent<number>) => {
    setCompaniesPerPage(Number(event.target.value));
    setCurrentPage(1); 
  };

  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>;
  }

  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>;
  }

  return (
    <>
      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>Название</TableCell>
              <TableCell>Адрес</TableCell>
              <TableCell>Действия</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {companies.map((company) => (
              <TableRow key={company.id}>
                <TableCell>{company.id}</TableCell>
                <TableCell>{company.name}</TableCell>
                <TableCell>{company.address}</TableCell>
                <TableCell>
                  <Button component={Link} to={`/companies/${company.id}`}>
                    Просмотреть
                  </Button>
                  <Button component={Link} to={`/companies/edit/${company.id}`}>
                    Редактировать
                  </Button>
                  <Button color="error">
                    Удалить
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '20px' }}>
        <FormControl variant="standard" sx={{ minWidth: 120 }}>
          <InputLabel id="companies-per-page-label">Компаний на странице</InputLabel>
          <Select
            labelId="companies-per-page-label"
            id="companies-per-page"
            value={companiesPerPage}
            onChange={handleChangeRowsPerPage}
            label="Компаний на странице"
          >
            <MenuItem value="10">10</MenuItem>
            <MenuItem value="25">25</MenuItem>
            <MenuItem value="50">50</MenuItem>
          </Select>
        </FormControl>
        <Pagination count={Math.ceil(totalCompanies / companiesPerPage)} page={currentPage} onChange={handleChangePage} />
      </div>
      <div style={{ marginTop: '20px' }}>
        <Button component={Link} to="/companies/create" variant="contained" color="secondary">
          Создать компанию
        </Button>
      </div>
    </>
  );
};

export default CompanyList;